<?php
/**
 * @version 4.00 $Id: autofbook.php 31 2012-03-27 10:47:44Z webbochsant@gmail.com $
 * @package AutoFBook Plugin
 * @author    Daniel Eliasson Stilero AB - http://www.stilero.com
 * @copyright	Copyright (c) 2011 Stilero AB. All rights reserved.
 * 	@license	GPLv2
* 	Joomla! is free software. This version may have been modified pursuant
* 	to the GNU General Public License, and as distributed it includes or
* 	is derivative of works licensed under the GNU General Public License or
* 	other free or open source software licenses.
 * 
 *  This file is part of AutoFBook Plugin.

    AutoFBook Plugin is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    AutoFBook Plugin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with AutoFBook Plugin.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.plugin.plugin');

// Initiate class to hold plugin events
class plgSystemAutofbook extends JPlugin {
    var $config;
    var $inBackend;
    var $error = false;
    var $CheckClass;
    var $fbAppID;
    var $fbAppSecret;
    var $fbOauthAccessToken;
    var $fbOauthCode;
    var $classes;
    const HTTP_STATUS_OK = '200';
    const ERROR_RETURNURL_NOT_SPECIFIED = '10';
    const ERROR_AUTHTOKENURL_NOT_SPECIFIED = '11';
    const ERROR_URL_NOT_VALID = '12';
    const ERROR_POST_FAIL = '13';
    const ERROR_COMMUNICATION_FAULT = '14';
    const ERROR_OAUTH_EXCEPTION = '50';
    const ERROR_OAUTH_OTHER = '55';



    //Facebook parameters
    var $access_token_url = "https://www.facebook.com/dialog/oauth"; 
    var $authorization_scope = "scope=publish_stream,share_item,offline_access,manage_pages";
    var $authorization_response_type = "response_type=token";
    var $ogTagsAdded = false;

    public function plgSystemAutofbook( &$subject, $config ) {
        parent::__construct( $subject, $config );
        $language = JFactory::getLanguage();
        $language->load('plg_system_autofbook', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('plg_system_autofbook', JPATH_ADMINISTRATOR, null, true);
        $this->fbAppID = $this->params->def('fb_app_id');
        $this->fbAppSecret = $this->params->def('fb_app_secret');
        $this->fbOauthAccessToken = $this->params->def('auth_token');
        $this->fbOauthCode = $this->params->def('auth_code');
        $this->config = array(
            'shareLogTableName'     =>      '#__autofbook_posted',
            'pluginLangPrefix'      =>      'PLG_SYSTEM_AUTOFBOOK_',
            'pluginElement'         =>      'autofbook',
            'classFolder'           =>      'autofbook'.DS.'classes',
            'articleClass'          =>      'jArticle',
            'articleClassFile'      =>      'jArticle.php',
            'fbookClass'            =>      'FBookClass',
            'fbookClassFile'        =>      'fbookClass.php',
            'jfbClass'              =>      'JFBClass',
            'jfbClassFile'          =>      'jfbClass.php',
            'shareControllerClass'  =>      'stlShareControllerClass',
            'shareControllerFile'   =>      'stlShareControllerClass.php',
            'fbControllerClass'     =>      'fbControllerClass',
            'fbControllerFile'      =>      'fbControllerClass.php',
            'fbPageID'              =>      $this->params->def('fb_page_id'),
            'categoriesToShare'     =>      $this->params->def('section_id'),
            'shareDelay'            =>      $this->params->def('delay'),
            'articlesNewerThan'     =>      $this->params->def('items_newer_than'),
            'addOGTags'             =>      $this->params->def('add_ogtags'),
            'defaultOGImage'        =>      $this->params->def('og-img-default'),
            'inclOrExcl'            =>      $this->params->def('incl_excl')
        ); 
        $this->classes = array(
            'jArticle' => array(
                'name'=>'jArticle',
                'file'=>'jArticle.php'
            ),
            'FBookClass' => array(
                'name'=>'FBookClass',
                'file'=>'fbookClass.php'
            ),
            'JFBClass' => array(
                'name'=>'JFBClass',
                'file'=>'jfbClass.php'
            ),
            'stlShareControllerClass' => array(
                'name'=>'stlShareControllerClass',
                'file'=>'stlShareControllerClass.php'
            ),
            'fbControllerClass' => array(
                'name'=>'fbControllerClass',
                'file'=>'fbControllerClass.php'
            )
        );
        $this->preloadClasses();
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
    public function onContentAfterSave($context, &$article, $isNew) {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->inBackend = true;
        $this->prepareToPost($article);
        $this->postArticleToFB();
        return;
    }
    
    /**
     * Called after saving articles
     * 
     * @param type $article
     * @param type $isNew
     * @return void
     * @since 1.5
     */
    public function onAfterContentSave( &$article, $isNew ) {
       $this->inBackend = true;
        $this->prepareToPost($article);
        $this->postArticleToFB();
        return;
    }

    /**
     * Called when articles are viewed in the frontend.
     * @param type $article
     * @param type $params
     * @param type $limitstart
     * @return void
     * @since 1.5
     */
    function onAfterDisplayContent( & $article, & $params, $limitstart=0) {
        $this->inBackend = false;
        $this->prepareToPost($article);
        $this->postArticleToFB();
        return;
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
        $this->inBackend = false;
        $this->prepareToPost($article);
        $this->postArticleToFB();
        return;
    }
  
    /**
     * Called in the frontend before the articles are rendered
     * @param type $article
     * @param type $params
     * @param type $limitstart
     * @return void
     * @since 1.5
     */
    function onPrepareContent(  &$article, &$params, $limitstart=0 ) {
        $this->insertOGTags($article);
        return;
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
    function onContentPrepare(  $context, &$article, &$params, $page=0 ) {
        $this->insertOGTags($article);
        return;
    }
    
//    function onAfterDispatch( ){
//        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
//        $actualSignature = JRequest::getVar('signature');
//        $code = JRequest::getVar('code');
//        if( ($code == '') || ($actualSignature == '') ){
//            return;
//        }
//        if(!is_object($this->CheckClass)){
//                $this->setupClasses();
//        }
//        $expectedSignature = rawurldecode($this->CheckClass->getRequestSignature());
//        if( $actualSignature != $expectedSignature){
//            JError::raiseNotice( 0,'The signature sent to Facebook differs from the one received.' );
//             if (JDEBUG) JError::raiseNotice( 0,'Expected: '.$expectedSignature );
//             if (JDEBUG) JError::raiseNotice( 0,'Actual: '.$actualSignature );   
//            return;
//        }
//        if($code != '' ){
//            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." code: ".$code );
//            $this->params->set('auth_code', $code.'#_=_');
//            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." param: ".$this->params->toString() );
//            if(!is_object($this->CheckClass)){
//                $this->setupClasses();
//            }
//            $this->CheckClass->storeParams($this->params, $this->config['pluginElement']);
//            $this->inBackend = true;
//            $this->displayMessage(JText::_($this->config['pluginLangPrefix'].'AUTHORIZED'), 'notice');
//        }
//    }
    
    private function prepareToPost($article){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->setupClasses();
        $articleFactory = new jArticle($article);
        $articleObject = $articleFactory->getArticleObj();
        //$articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        $this->CheckClass->setArticleObject($articleObject);        
    }
    
    private function preloadClasses(){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $classFolder = $this->config['classFolder'];
        foreach ($this->classes as $class) {
            JLoader::register(
                $class['name'], 
                dirname(__FILE__).DS.$classFolder.DS.$class['file']
            );
        }
    }
    
    private function setupClasses() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        //$this->preloadClasses();
        //$token = $this->params->def('fbpage_auth_token') == '' ? $this->fbOauthAccessToken : $this->params->def('fbpage_auth_token');
        $this->CheckClass = new $this->classes['fbControllerClass']['name']( 
            array(
                'fbAppID'               =>      $this->fbAppID,
                'fbAppSecret'           =>      $this->fbAppSecret,
                'fbOauthAccessToken'    =>      $this->fbOauthAccessToken,
                'fbOauthCode'           =>      $this->fbOauthCode,
                'fbPageID'              =>      $this->config['fbPageID'],
                'shareLogTableName'     =>      $this->config['shareLogTableName'],
                'pluginLangPrefix'      =>      $this->config['pluginLangPrefix'],
                'categoriesToShare'     =>      $this->config['categoriesToShare'],
                'shareDelay'            =>      $this->config['shareDelay'],
                'articlesNewerThan'     =>      $this->config['articlesNewerThan'],
                'inBackend'             =>      $this->inBackend,
                'inclOrExcl'            =>      $this->config['inclOrExcl']
            )
        );
    }
    
    private function postArticleToFB() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if( !$this->isInitialChecksOK() ) {
            $this->displayMessage(JText::_($this->CheckClass->error['message']) , $this->CheckClass->error['type']);
            return;
        }
        if ( $this->CheckClass->shareLinkToFB() ) {
            $this->displayMessage(JText::_($this->config['pluginLangPrefix'].'OK'));
            $token = $this->CheckClass->fbClass->getOauthAccessToken();
            $this->params->set('auth_code', '');
            $this->params->set('auth_token', $token);
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." param: ".$this->params->toString() );
            if(!is_object($this->CheckClass)){
                $this->setupClasses();
            }
            $this->CheckClass->storeParams($this->params, $this->config['pluginElement']);
            return;
        }else if( ($this->CheckClass->fbClass->getErrorCode() == self::ERROR_OAUTH_EXCEPTION) && $this->CheckClass->fbClass->getOauthAccessToken() != "" ){
            $this->params->set('auth_code', '');
            $this->params->set('auth_token', '');
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." reseted Tokens and Code " );
            if(!is_object($this->CheckClass)){
                $this->setupClasses();
            }
            $this->CheckClass->storeParams($this->params, $this->config['pluginElement']);
            $this->displayMessage(JText::_($this->config['pluginLangPrefix'].'OAUTHEXCEPT_RESETED'));
            return;
        }else{
            $this->displayMessage(JText::_($this->CheckClass->error['message']) , $this->CheckClass->error['type']);
            return;
        }
    }
    
    private function doInitialChecks() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->CheckClass->isServerSupportingRequiredFunctions();
        $this->CheckClass->isServerSafeModeDisabled();
        $this->CheckClass->isFBAppDetailsEntered();
        $this->CheckClass->isArticleObjectIncluded();
        //$this->CheckClass->isItemActive();
        $this->CheckClass->isItemPublished();
        $this->CheckClass->isItemNewEnough();
        $this->CheckClass->isItemPublic();
        $this->CheckClass->isCategoryToShare();
        $this->CheckClass->prepareTables();
        $this->CheckClass->isSharingToEarly();
        $this->CheckClass->isItemAlreadyShared();
        return $this->CheckClass->error;
    }

    public function isInitialChecksOK() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $errorMessage = $this->doInitialChecks();
        if ( $errorMessage != FALSE ) {
            return FALSE;
        }
        return TRUE;
    }

//    public function getArticleObjectFromJoomlaArticle($joomlaArticle) {
//        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
//        $articleObject = new stdClass();
//        $articleObject->id = $joomlaArticle->id;
//        $articleObject->language= (isset($joomlaArticle->language))? $joomlaArticle->language : '';
//        $articleObject->link = $joomlaArticle->alias;
//        $articleObject->full_url = $this->getFullURL($joomlaArticle->id);
//        $articleObject->tags = $this->getArticleTagsArray($joomlaArticle->metakey);
//        $articleObject->title = $joomlaArticle->title;
//        $articleObject->catid = $joomlaArticle->catid;
//        $articleObject->access = $joomlaArticle->access;
//        $articleObject->publish_up = $joomlaArticle->publish_up;
//        $articleObject->published = $joomlaArticle->state; 
//        return $articleObject;
//    }

//    public function getFullURL($articleID) {
//        $urlQuery = "?option=com_content&view=article&id=".$articleID;
//        $fullURL = JURI::root()."index.php".$urlQuery;
//        return $fullURL;
//    }
    
//    private function getArticleTagsArray($commaSpearatedMetaKeys) {
//        if($commaSpearatedMetaKeys == ""){
//            return;
//        }
//       $metaKeyArray = explode(",", $commaSpearatedMetaKeys);
//       foreach ($metaKeyArray as $key => $value) {
//           $tagsArray[] = trim(str_replace(" ", "", $value));
//       }
//       return $tagsArray;
//    }

    private function insertOGTags($articleObj){
        if( $this->params->def('add_ogtags')==0 || !isset($articleObj->id) || $this->ogTagsAdded ){
            return;
        }
        $document = JFactory::getDocument();
        $metaDataArray = $this->getMetaDataArray($articleObj);
        foreach ($metaDataArray as $key => $value) {
            if($value !="") {
                $metaProp = '<meta property="'.$key.'" content="'.$value.'" />';
                $document->addCustomTag($metaProp);
            }
        }
        $this->ogTagsAdded = true;
    }

    private function getMetaDataArray($articleObj){
        $articleFactory = new jArticle($articleObj);
        $article = $articleFactory->getArticleObj();
        $imageSrc = $article->image; 
        $imageSrc = ( $imageSrc != '' )? $imageSrc : JURI::root().'images'.DS.$this->params->def('og-img-default');
        $descNeedles = array("\n", "\r", "\"", "'");
        //$desc = (isset($article->description) )? strip_tags( str_replace($descNeedles, " ", $article->description )) : "";
        $desc = (isset($article->description) )? $article->description  : "";
        $joomlaConfig = JFactory::getConfig();
        $joomlaSiteName = $joomlaConfig->getValue( 'config.sitename' );
        $langObj = JFactory::getLanguage();
        $currentLang = str_replace( "-", "_", $langObj->getTag() );
        $metaData = array(
            'og:title'          =>  htmlentities(strip_tags( $article->title), ENT_QUOTES, "UTF-8"),
            'og:image'          =>  $imageSrc,
            'og:description'    =>  $desc,
            'og:locale'         =>  $currentLang,
            'og:type'           =>  'article',
            'article:published_time'    =>  date('c', strtotime( $article->publish_up) ),
            'article:section'           =>  htmlentities(strip_tags($article->category_title), ENT_QUOTES, "UTF-8"),
            'article:modified_time'     =>  date('c', strtotime( $article->modified) ),
            'og:site_name'      =>  $joomlaSiteName,
            'og:url'            =>  $article->url
        );
        return $metaData;
    }

//    private function getImageSrcFromContent($articleObj) {
//        preg_match_all('/<img[^>]+>/i',$articleObj->text, $imageNodes);
//
//        $img = array();
//        foreach( $imageNodes as $imgTag) {
//            $pattern = "/src=[\"']?([^\"']?.*(png|jpg|gif))[\"']?/i";
//            preg_match_all($pattern, $articleObj->text, $img);
//        }
//        $firstImageSrc = "";
//        if( !empty($img) ) {
//            $imgArr = $img[1];
//            $firstImageSrc = (isset($imgArr[0])) ? $this->getAbsUrl( $imgArr[0] ) : null;
//        }
//        return $firstImageSrc; 
//    }

//    private function getAbsUrl($url){
//        if($url == "") return;
//        $parsedURL = parse_url($url);
//        $query = ( isset($parsedURL['query']) ) ? "?".$parsedURL['query'] : "" ;
//        $path = $parsedURL['path'];
//        if(strlen($parsedURL['path'])>2){
//            $path = (substr_count($parsedURL['path'] , '/', 0, 1))? substr($parsedURL['path'], 1) : $parsedURL['path'];
//        }
//        $absURL = JURI::root().$path.$query;
//        return $absURL;
//    }
    
    public function displayMessage($msg, $messageType = "") {
        if(JDEBUG) JFactory::getApplication()->enqueueMessage( $msg, $messageType);
        $isSetToDisplayMessages = ($this->params->def('pingmessages')==0)?false:true;
        if( ! $isSetToDisplayMessages || ! $this->inBackend ){
            return;
        }
        if($messageType == 'notice'){
            $this->showNotice($msg);
            return;
        }else if($messageType == 'warning'){
            $this->showWarning($msg);
            return;
        }
        JFactory::getApplication()->enqueueMessage( $msg, $messageType);
    }
    
    public function showNotice($msg, $errorCode=0) {
        if(JDEBUG) JError::raiseNotice( $errorCode, $msg );
        $isSetToDisplayMessages = ($this->params->def('pingmessages')==0)?false:true;
        if( ! $isSetToDisplayMessages || ! $this->inBackend ){
            return;
        }else{
             JError::raiseNotice( $errorCode, $msg );
        }
    }
    
    public function showWarning($msg, $errorCode=0) {
        if(JDEBUG) JError::raiseWarning( $errorCode, $msg );
        $isSetToDisplayMessages = ($this->params->def('pingmessages')==0)?false:true;
        if( ! $isSetToDisplayMessages || ! $this->inBackend ){
            return;
        }else{
             JError::raiseWarning( $errorCode, $msg );
        }
    }
    
} // END CLASS

