<?php
/**
 * @version 5.2 2013-12-22
 * @package AutoFBook Plugin
 * @author    Daniel Eliasson Stilero AB - http://www.stilero.com
 * @copyright	Copyright (c) 2011 Stilero AB. All rights reserved.
 * @license	GPLv2
 * 
*/

// no direct access
defined('_JEXEC') or die ('Restricted access');
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}

//jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');
JLoader::register('StileroAFBHelper', dirname(__FILE__).DS.'helper.php');

// Initiate class to hold plugin events
class PlgSystemAutofbook extends JPlugin {
    protected $Facebook;
    protected $AccessToken;
    protected $Feed;
    protected $Table;
    protected $Article;
    protected $ShareCheck;
    protected $OpenGraph;
    protected $inBackend;
    protected $pageId;
    protected $adminId;
    protected $_appId;
    protected $_appSecret;
    protected $_authToken;
    protected $_fbpageAuthToken;
    protected $_addOgTags;
    protected $_ogImageDefault;
    protected $_inclOrExcl;
    protected $_delay;
    protected $_dateLimit;
    protected $_catList;
    protected $_allwaysPostOnSave;
    protected $_isBackend;
    
    const HTTP_STATUS_OK = '200';
    const ERROR_RETURNURL_NOT_SPECIFIED = '10';
    const ERROR_AUTHTOKENURL_NOT_SPECIFIED = '11';
    const ERROR_URL_NOT_VALID = '12';
    const ERROR_POST_FAIL = '13';
    const ERROR_COMMUNICATION_FAULT = '14';
    const ERROR_OAUTH_EXCEPTION = '50';
    const ERROR_OAUTH_OTHER = '55';
    const TABLE_NAME = '#__autofbook_log';
    const LANG_PREFIX = 'PLG_SYSTEM_AUTOFBOOK_';
    const CLASS_PREFIX = 'StileroAFB';
    
    public function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        $language = JFactory::getLanguage();
        $language->load('plg_system_autofbook', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('plg_system_autofbook', JPATH_ADMINISTRATOR, null, true);
        StileroAFBHelper::importClasses();
        $this->setParams();
    }
    
    /**
     * Reads the params and sets them in the class 
     */
    protected function setParams(){
        $this->_delay = $this->params->def('delay');
        $this->_dateLimit = $this->params->def('items_newer_than');
        $this->_catList = $this->params->def('section_id');
        $this->_addOgTags = $this->params->def('add_ogtags');
        $this->_ogImageDefault = JURI::root().'images/'.$this->params->def('og-img-default');
        $this->_inclOrExcl = $this->params->def('incl_excl');
        $this->adminId = $this->params->def('fbadmin_id');
        $this->pageId = $this->params->def('fb_pages');
        $this->_appId = $this->params->def('fb_app_id');
        $this->_appSecret = $this->params->def('fb_app_secret');
        $this->_authToken = $this->params->def('auth_token');
    }
    
    /**
     * Prepares for a FB Page call
     */
    protected function initFBPageCall(){
        $response = $this->Facebook->User->getTokenForPageWithId($this->pageId);
        $this->_fbpageAuthToken = StileroFBOauthResponse::handle($response);
        $this->Facebook->Feed->setToken($this->_fbpageAuthToken);
        $this->Facebook->Feed->setUserId($this->pageId);
    }
    
    /**
     * Checks if the post is to a personal wall or a page. It compares the page id
     * with the admin id, and if they mat
     * @return boolean true if personal post
     */
    protected function isPersonalPost(){
            $this->Facebook->User->setUserId('me');
            $me = $this->Facebook->User->me();
            $user = StileroFBOauthResponse::handle($me);
            if($user->id == $this->pageId){
                $this->Facebook->Feed->setUserId('me');
                $this->params->set('fb_pages', '');
                StileroAFBPluginparamshelper::storeParams($this->params, 'autofbook');
                return true;
            }
            
        
    }
    
    /**
     * Initializes the classes and the FB-connection
     * @param stdClass $article Joomla article/item object
     * @param string $option the joomla option (com_content/com_k2) use the constants of the JArticle class
     */
    protected function init($article, $option){
        $this->Table = new StileroAFBTable(self::TABLE_NAME);
        $categories = $this->_catList==''? array() : explode(',', $this->_catList);
        $this->ShareCheck = new StileroAFBSharecheck($article, $this->Table, $this->_delay, $this->_dateLimit, $categories, $this->_allwaysPostOnSave, $this->_isBackend, $option);
        $redirectUri = JURI::root();
        $this->Facebook = new StileroFBFacebook($this->_appId, $this->_appSecret, $redirectUri);
        $this->Facebook->setAccessTokenFromToken($this->_authToken);
        $this->Facebook->init();
        $this->isPersonalPost();
        if($this->pageId != ''){
            if(!$this->isPersonalPost()){
                $this->initFBPageCall();
            }
        }else{
            $this->Facebook->Feed->setUserId('me');
        }
        $this->Article = $this->ShareCheck->getJArticle();
    }

    /**
     * Stores the updated token to the plugin settings
     */
    protected function storeNewToken(){
        $newToken = $this->Facebook->getToken();
        $this->params->set('auth_token', $newToken);
        StileroAFBPluginparamshelper::storeParams($this->params, 'autofbook');
    }
    
    /**
     * Wraps up after a call. Shows messages and updates tokens
     * @param string $response JSON response from FB
     */
    protected function wrapUp($response){
        $postResponse = StileroFBOauthResponse::handle($response);
        if(isset($postResponse->id)){
            //$this->storeNewToken();
            $message = JText::_(self::LANG_PREFIX.'SUCCESS');
        }else if($postResponse == null){
            $message = JText::_(self::LANG_PREFIX.'NULL');
            $this->Table->deleteLog($this->Article->id, $this->Article->catid, $this->Article->url, $this->Article->lang, $this->Article->component);
        }else{
            $message = JText::_(self::LANG_PREFIX.'FAIL');
            $this->Table->deleteLog($this->Article->id, $this->Article->catid, $this->Article->url, $this->Article->lang, $this->Article->component);
        }
        $this->showMessage($message);
    }
    
    /**
     * Posts a link to FB
     * @param string $context
     * @param stdClass $article
     * @param string $option
     */
    protected function postLink($context, $article, $option){
        if(StileroAFBContextHelper::isArticle($context)){   
            $this->init($article, $option);
            if($this->ShareCheck->hasFullChecksPassed() ){
                $this->Table->saveLog($this->Article->id, $this->Article->catid, $this->Article->url, $this->Article->lang, $this->Article->component);
                $link = $this->Article->url;
                $title = $this->Article->title;
                $caption = $this->Article->description;
                $image = $this->Article->image;
                $response = $this->Facebook->Feed->postLink($link, $title, '', $caption, $image);
                $this->wrapUp($response);
             }
        }
    }
    
    /**
     * Displays a Joomla message in backend.
     * @param string $message The message to display
     * @param string $type The type of message
     */
    protected function showMessage($message, $type='message'){
        $translatedMessage = JText::_($message);
        if($this->_isBackend){
            StileroAFBMessageHelper::show($translatedMessage, $type);
        }
    }
        
    /**
     * Called after saving articles
     * 
     * @param type $context
     * @param type $article
     * @param type $isNew
     * @return void
     * @since 1.6
     */
    public function onContentAfterSave($context, $article, $isNew) {
        $this->_isBackend = true;
        $option = 'com_content';
        if($context == StileroAFBContextHelper::K2_ITEM){
            $option = 'com_k2';
        }
        if(StileroAFBContextHelper::isArticle($context)){
            $this->postLink($context, $article, $option); 
        }          
    }
      
    public function onAfterK2Save(&$article, $isNew){
        $this->_isBackend = true;
        $option = 'com_k2';
        $this->postLink($context, $article, $option);    
    }
 
    /**
     * Called when articles are viewed in the frontend
     * @param type $article
     * @param type $params
     * @param type $limitstart
     * @return void
     * @since 1.6
     */
    function onContentAfterDisplay( $context, &$article, &$params, $limitstart=0) {
        $this->_isBackend = FALSE;
        $option = 'com_content';
        if($context == StileroAFBContextHelper::K2_ITEM){
            $option = 'com_k2';
        }
        if(StileroAFBContextHelper::isArticle($context)){
            $this->postLink($context, $article, $option); 
        }
    }

    /**
     * Called in the frontend before the articles are rendered
     * @param type $context
     * @param type $article
     * @param type $params
     * @param type $page
     * @return void
     * @since 1.6
     */    

    function onContentPrepare( $context, &$article, &$params, $page=0 ) {
        if(StileroAFBContextHelper::isArticle($context) && $this->_addOgTags){
            $this->OpenGraph = new StileroFBOpengraph($article);
            $this->OpenGraph->setDefaultImage($this->_ogImageDefault);
            $this->OpenGraph->addTags();
        }
        return;
    }
    
} // END CLASS

