<?php
/**
 * @version 3.51 $Id: autofbook.php 31 2012-03-27 10:47:44Z webbochsant@gmail.com $
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
            'classFolder'           =>      'autofbookclasses',
            'articleClass'          =>      'Article',
            'articleClassFile'      =>      'article.php',
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
            'addOGTags'             =>      $this->params->def('add_ogtags')
        ); 
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
    
    function onAfterDispatch( ){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $actualSignature = JRequest::getVar('signature');
        $code = JRequest::getVar('code');
        if( ($code == '') || ($actualSignature == '') ){
            return;
        }
        if(!is_object($this->CheckClass)){
                $this->setupClasses();
        }
        $expectedSignature = rawurldecode($this->CheckClass->getRequestSignature());
        if( $actualSignature != $expectedSignature){
            JError::raiseNotice( 0,'The signature sent to Facebook differs from the one received.' );
             if (JDEBUG) JError::raiseNotice( 0,'Expected: '.$expectedSignature );
             if (JDEBUG) JError::raiseNotice( 0,'Actual: '.$actualSignature );   
            return;
        }
        if($code != '' ){
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." code: ".$code );
            $this->params->set('auth_code', $code.'#_=_');
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." param: ".$this->params->toString() );
            if(!is_object($this->CheckClass)){
                $this->setupClasses();
            }
            $this->CheckClass->storeParams($this->params, $this->config['pluginElement']);
            $this->inBackend = true;
            $this->displayMessage(JText::_($this->config['pluginLangPrefix'].'AUTHORIZED'), 'notice');
        }
    }
    
    private function prepareToPost($article){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->setupClasses();
        $articleObject = $this->getArticleObjectFromJoomlaArticle($article);
        $this->CheckClass->setArticleObject($articleObject);        
    }
 
    private function setupClasses() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $folder = $this->config['classFolder'];
        $classNames = array(
            $this->config['fbookClass'],
            $this->config['jfbClass'],
            $this->config['shareControllerClass'],
            $this->config['fbControllerClass'],
            $this->config['articleClass']
        );
        $classFiles = array(
            $this->config['fbookClassFile'],
            $this->config['jfbClassFile'],
            $this->config['shareControllerFile'],
            $this->config['fbControllerFile'],
            $this->config['articleClassFile']
        );
        $classFolder = $this->config['classFolder'];
        for($i=0;$i<count($classNames);$i++){
            JLoader::register(
                $classNames[$i], 
                dirname(__FILE__).DS.$classFolder.DS.
                $classFiles[$i]
            );
        }
        $this->CheckClass = new $this->config['fbControllerClass']( 
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
                'inBackend'             =>      $this->inBackend
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
        $this->CheckClass->isItemActive();
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

    public function getArticleObjectFromJoomlaArticle($joomlaArticle) {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $articleObject = new stdClass();
        $articleObject->id = $joomlaArticle->id;
        $articleObject->language= (isset($joomlaArticle->language))? $joomlaArticle->language : '';
        $articleObject->link = $joomlaArticle->alias;
        $articleObject->full_url = $this->getFullURL($joomlaArticle->id);
        $articleObject->tags = $this->getArticleTagsArray($joomlaArticle->metakey);
        $articleObject->title = $joomlaArticle->title;
        $articleObject->catid = $joomlaArticle->catid;
        $articleObject->access = $joomlaArticle->access;
        $articleObject->publish_up = $joomlaArticle->publish_up;
        $articleObject->published = $joomlaArticle->state; 
        return $articleObject;
    }

    public function getFullURL($articleID) {
        $urlQuery = "?option=com_content&view=article&id=".$articleID;
        $fullURL = JURI::root()."index.php".$urlQuery;
        return $fullURL;
    }
    
    private function getArticleTagsArray($commaSpearatedMetaKeys) {
        if($commaSpearatedMetaKeys == ""){
            return;
        }
       $metaKeyArray = explode(",", $commaSpearatedMetaKeys);
       foreach ($metaKeyArray as $key => $value) {
           $tagsArray[] = trim(str_replace(" ", "", $value));
       }
       return $tagsArray;
    }

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
        $imageSrc = $this->getImageSrcFromContent($articleObj); 
        $imageSrc = ( isset($imageSrc) )? $imageSrc : "";
        $descNeedles = array("\n", "\r", "\"", "'");
        $desc = (isset($articleObj->introtext) )? strip_tags( str_replace($descNeedles, "", $articleObj->introtext )) : "";
        $joomlaConfig = JFactory::getConfig();
        $joomlaSiteName = $joomlaConfig->getValue( 'config.sitename' );
        $langObj = JFactory::getLanguage();
        $currentLang = str_replace( "-", "_", $langObj->getTag() );
        $metaData = array(
            'og:title'          =>  $articleObj->title,
            'og:image'          =>  $imageSrc,
            'og:description'    =>  $desc,
            'og:locale'         =>  $currentLang,
            'og:type'           =>  'website',
            'og:site_name'      =>  $joomlaSiteName,
            'og:url'            =>  $this->getFullURL($articleObj->id)
        );
        return $metaData;
    }

    private function getImageSrcFromContent($articleObj) {
        preg_match_all('/<img[^>]+>/i',$articleObj->text, $imageNodes);

        $img = array();
        foreach( $imageNodes as $imgTag) {
            $pattern = "/src=[\"']?([^\"']?.*(png|jpg|gif))[\"']?/i";
            preg_match_all($pattern, $articleObj->text, $img);
        }
        $firstImageSrc = "";
        if( !empty($img) ) {
            $imgArr = $img[1];
            $firstImageSrc = (isset($imgArr[0])) ? $this->getAbsUrl( $imgArr[0] ) : null;
        }
        return $firstImageSrc; 
    }

    private function getAbsUrl($url){
        if($url == "") return;
        $parsedURL = parse_url($url);
        $query = ( isset($parsedURL['query']) ) ? "?".$parsedURL['query'] : "" ;
        $path = $parsedURL['path'];
        if(strlen($parsedURL['path'])>2){
            $path = (substr_count($parsedURL['path'] , '/', 0, 1))? substr($parsedURL['path'], 1) : $parsedURL['path'];
        }
        $absURL = JURI::root().$path.$query;
        return $absURL;
    }
    
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
    
//	public function onContentAfterSave($context, &$article, $isNew)
//    {
//		//Since J1.5 does not allow sql to be run from the manifest at installation,
//		// We need this workaround that creates tables if there not found
//                $this->inBackend = true;
//		if(version_compare(JVERSION,'1.5.0','ge')) {
//			//check if a table is created
//			if(!$this->_tableExists()){
//				$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_NOTABLES'));
//				if($this->_createTables()){
//					//Set the parameter after the plugin is created
//					$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_CREATEDTABLES'));
//				}else{
//					$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_TABLESFAILED'), 'error');
//				}
//			}
//		}
//		
//		//If the Authorize settings are activated, we need to authorize the app throuch facebook.
//		if ($this->params->def('authorize_app')) {
//			$this->_displayMessage(JText::_('Authorize on'));
//			$this->_authorizeApp($article->id);
//			return FALSE;
//		}
//		$this->_displayMessage(JText::_('Authorize off'));
//		$this->_initiatePosting($article);
//		
//		return;
//	}
	
	/**
     * This is the main method for the plugin. It creates necessary links and makes all calls to the pingfunctions.
     * 
	 * @param	object	&$article	The article object.  Note $article->text is also available
	 * @param	boolean	$isNew		Boolean showing if the article is new
	 * 
	 * @return	void
	 * 
	 * @since	1.5
     */
//	public function onAfterContentSave( &$article, $isNew )
//	{
//                $this->inBackend = true;
//		//Since J1.5 does not allow sql to be run from the manifest at installation,
//		// We need this workaround that creates tables if there not found
//		if(version_compare(JVERSION,'1.5.0','ge')) {
//			//check if a table is created
//			if(!$this->_tableExists()){
//				$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_NOTABLES'));
//				if($this->_createTables()){
//					//Set the parameter after the plugin is created
//					$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_CREATEDTABLES'));
//				}else{
//					$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_TABLESFAILED'), 'error');
//				}
//			}
//		}
//		
//		//If the Authorize settings are activated, we need to authorize the app throuch facebook.
//		if ($this->params->def('authorize_app')) {
//			$this->_authorizeApp($article->id);
//			return FALSE;
//		}
//		$this->_initiatePosting($article);
//		
//		return;
//	}
	

	/**
     * Triggered when the item editing is initiated
	 * Picks up the token result from Facebook, and saves to a parameter
     * 
	 * @param	object	&item		The article object.  Note $article->text is also available
	 * @param	string	$type		Admin form type
	 * @param	string	$category	Admin form category
	 * 
	 * @return	void
	 * 
	 * @since	1.5
     */
//	function onRenderAdminForm(&$item, $type, $category ){
//		
//		global $mainframe;
//                $this->inBackend = true;
//		$mainframe = &JFactory::getApplication();
//		
//		// Get the K2 plugin params (the stuff you see when you edit the plugin in the plugin manager)
//		$plugin = & JPluginHelper::getPlugin('k2', 'autofbook');
//		$this->pluginParams = new JParameter($plugin->params);
//		
//		// Get the output of the K2 plugin fields (the data entered by your site maintainers)
//		if(JVERSION == '15') {
//			$plugins = new K2Parameter($item->plugins, '', $this->pluginName);
//		}else {
//			$plugins = new JParameter($item->plugins, '', $this->pluginName);
//		}
//		
//		
//		//Make sure we don't trigger multiple times
//		if( ( $type != "item") || ($category != "content") ){
//			return;
//		}
//		//Pick up the token returned from facebook
//		$auth_made = JRequest::getVar('auth_made');
//		//$token = JURI::getInstance()->toString();
//		//$token = $_SERVER['HTTP_REFERER'];
//		if($auth_made == "yes"){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_FB_ACCESS_TOKEN_RECEIVED1'));
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_FB_ACCESS_TOKEN_RECEIVED2'));
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_FB_ACCESS_TOKEN_RECEIVED3'));
//		}
//		
//		return;
//	}

	/**
     * Triggered when an item is displayed in the frontend
     * 
	 * @param	object	&item		The article object.  Note $article->text is also available
	 * @param	object	$params		Parameter object
	 * @param	integer	$limitstart	Paging
	 * 
	 * @return	void
	 * 
	 * @since	1.5
     */
//	function onAfterDisplayContent( & $article, & $params, $limitstart) {
//                $this->inBackend = false;
//		$mainframe = &JFactory::getApplication();
//		//Check if we are ready to ping, and begin.
//		$this->_initiatePosting($article);
//		return;
//		
//		//return $output;
//	}
		/**
     * Triggered when an item is displayed in the frontend
     * 
	 * @param	object	&item		The article object.  Note $article->text is also available
	 * @param	object	$params		Parameter object
	 * @param	integer	$limitstart	Paging
	 * 
	 * @return	void
	 * 
	 * @since	1.6
     */
//	function onContentAfterDisplay( $article, &$params, $limitstart) {
//                $this->inBackend = false;
//		$this->_initiatePosting($article);
//		return;
//		
//		//return $output;
//	}
//        



        /**
	 * This method takes care of initiating the posting.
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param integer	$itemid		K2 item ID
	 * 
	 * @return none
	 */
//	function _initiatePosting($article)
//	{
//		//Now do all the initial tests before posting to Facebook
//		if($this->_checkBeforeUpdate($article)){
//			
//			//Create a routed URL to the item
//			$fullUrl = $this->_getItemUrl($article);
//			$fullTextMessage = $article->title;
//			$article_description = ($article->metadesc == "")?$article->introtext:$article->metadesc;
//			$this->_saveFacebooked($article);
//			if($this->_updateFacebookStatus($article->title, $fullUrl, $article_description)){	
//				$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_POSTCOMPLETED')." ".$article->title);
//			}else{
//				//Posting failed, delete from db
//				$this->_deleteArticlePost($article);
//			}
//		}
//		return;
//	}
	/**
	 * This method takes care of the Facebook Authorization.
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param integer	$itemid		K2 item ID
	 * 
	 * @return none
	 */
	function _authorizeApp($itemid){
		//global $mainframe;
		//Build a redirect URL
		$redirect_url = JURI::current().
			"?option=com_content".
			"&task=edit".
			"&cid[]=".$itemid.
			"&auth_made=yes";
		
		//Construct the URL
		$authorization_url = $this->access_token_url.
			"?" . $this->authorization_scope.
			"&" . $this->authorization_response_type.
			"&client_id=" . $this->params->def('fb_app_id').
			"&redirect_uri=" . urlencode($redirect_url);
		
		//Redirect to the FB authorization
                $app = JFactory::getApplication();
                $app->redirect( $authorization_url );

	}
	/**
	 * This method returns a Joomla url based on article and id.
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param object	&$article	content object.
	 * 
	 * @return string	article url
	 */
//	function _getItemUrl($article)
//	{
//		//Include K2 path
//		//JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
//		
//		$link = JURI::root().
//			"index.php?option=com_content&view=article&id=".$article->id;
//		
//		return $link;
//	}
	/**
	 * This method returns a Joomla url based on article and id.
	 * 
	 * DEPRECATED
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param object	&$article	content object.
	 * 
	 * @return string	article url
	 */
//	function _createUrl($article)
//	{
//
//				//Remove administrator trash. TODO: make this remove itself
//				$link = $article->link;
//				
//				//Remove the irritating trailing slash that root produces
//				$link = str_replace("/component/content/article", "", $link) ;
//				
//				
//				//Make host url without the ending slash
//				$hosturl = substr(JURI::root(), 0, strlen(JURI::root())-1 );
//				//Make sure the host is not in the current link, since well add it later.
//				//This is required for some SEF-plugins that add the host.
//				$link = str_replace($hosturl, "", $link);
//				//Add the hosturl again
//				$link = $hosturl.$link;
//				
//				//Decode the url to work universally
//				$link = htmlentities($link);
//				
//				
//		return $link;
//	}
	
	
	/**
	 * Method makes some initial checks to see if we are ready to go.
	 * @param	object	&$article	The article object.  Note $article->text is also available
	 *
	 * @return	boolean
	 * @since	1.5
	 */
//	function _checkBeforeUpdate($article)
//	{
//		$publicAccess;
//		if (!is_object($article)  ){
//                    return FALSE;
//                }
//		
//		if(version_compare(JVERSION,'1.7.0','ge')) {
//			$publicAccess = 1;
//		} elseif(version_compare(JVERSION,'1.6.0','ge')) {
//			$publicAccess = 1;
//		} elseif(version_compare(JVERSION,'1.5.0','ge')) {
//			$publicAccess = 0;
//		}
//		
//		// Get plugin parameters set by the user
//		$fbappid = $this->params->def('fb_app_id');
//		$fbappsecret = $this->params->def('fb_app_secret');
//		$fbauth_token = $this->params->def('auth_token');
//		
//		$pingsection = $this->params->def('section_id');
//		$pingsectionarray = explode(",", $this->params->def('section_id'));
//		$items_newer_than = $this->params->def('items_newer_than');
//		
//                
//		//Check if fbappdetails are specified
//		if( $fbappid == "" || $fbappsecret == ""){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_NOLOGINDETAILS'), 'error');
//			return FALSE;
//		}
//
//		//Check if app is authorized
//		if( $fbauth_token == ""){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_NO_AUTHTOKEN'), 'error');
//			return FALSE;
//		}
//		
//		
//		//Article active?
//		if (($article->state!=1)){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_NOTACTIVE'));
//			return FALSE;
//		}
//		
//		//Article published?
//		$currentDate = $date =& JFactory::getDate();
//		//If the article should be published in the future, then we wont ping
//		if ( $article->publish_up > $date->toMySQL() ){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_FUTURE_PUBLISH'));
//			return FALSE;
//		}
//		
//		//Article older than the settings allows?
//		if( ( $article->publish_up <  $items_newer_than) && $items_newer_than !="" ){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_ITEM_OLD'));
//			return FALSE;
//		}
//		
//		//Is the article only for registred users?
//		if( $article->access != $publicAccess ){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_RESTRICT'));
//			return FALSE;
//		}
//		
//		
//		//Section to use for updates?
//		if ( $pingsection != "" ){
//			//The sectionlist is not empty, so we must check which categories to ping
//						
//			if ( !in_array($article->catid, $pingsectionarray) ){
//				$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_NOTSECTION'));
//				return FALSE;
//			}
//		}
//		
//             //Check if its to early to send an update
//	        if($this->_updateShouldBeDelayed($article)){
//                     return FALSE;
//	        }
//
//		//Finally check if the article is already sent
//		$isArticlePinged = $this->_checkArticlePostSent($article);
//		
//		//Now inverse and return
//		return !$isArticlePinged;
//	}
	
	
	/**
	 * This method checks if an article is already Facebooked and returns true or false
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param object	&$article	content object.
	 * 
	 * @return boolean	pinged
	 */
//	function _checkArticlePostSent($article){
//		
//		//Variables used for changes since 1.5
//		$sectionCat;	//Used for sections (1.5) or categories (1.6+)
//		$query;			//Used since joomla 1.6+ handles queries different from 1.5
//		
//		// Get a database object
//		$db		= &JFactory::getDbo();
//		
//		if(version_compare(JVERSION,'1.7.0','ge')) {
//			//Check if the current link has already been pinged
//			$query	= $db->getQuery(true);
//			$query->select('id');
//			$query->from('#__autofbook_posted');
//			//$query->where('itemlink='.$db->Quote($this->_createUrl($article)));
//			$query->where('article_id=' . $db->Quote($article->id). ' AND cat_id='.$db->Quote($article->catid));
//		
//		} elseif(version_compare(JVERSION,'1.6.0','ge')) {
//			//Check if the current link has already been pinged
//			$query	= $db->getQuery(true);
//			$query->select('id');
//			$query->from('#__autofbook_posted');
//			//$query->where('itemlink='.$db->Quote($this->_createUrl($article)));
//			$query->where('article_id=' . $db->Quote($article->id). ' AND cat_id='.$db->Quote($article->catid));
//		
//		}  elseif(version_compare(JVERSION,'1.5.0','ge')) {
//			$sectionCat = $article->sectionid;
//			$query = 'SELECT '
//						.$db->nameQuote('id').
//						' FROM '.$db->nameQuote('#__autofbook_posted').
//						' WHERE '.$db->nameQuote('article_id').'='.$article->id.
//						' AND '.$db->nameQuote('cat_id').'='.$db->Quote($article->catid);
//		}
//
//		$db->setQuery($query);
//		$result = $db->loadObject();
//		
//		//If the article is already pinged, return true otherwise false
//		if($result){
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_ALREADYSENT'));
//			return TRUE;
//		}
//		return FALSE;
//	}


	
	/**
	 * This method saves a post in the database to indicate that this article has been sent
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param object	$article	content object.
	 * 
	 * @return boolean	result
	 */
//	function _saveFacebooked($article){
//
//		$date=JFactory::getDate();
//		
//		//Create the Database class
//		$data =new stdClass();
//		$data->id = null;
//		$data->article_id = $article->id;
//		$data->cat_id = $article->catid;
//		$data->articlelink = $article->link;
//		
//		//Date working differently from J1.6
//		//$data->date = $date->toMySQL();
//		$data->date = date("Y-m-d H:i:s");
//
//		
//		$data->language = $article->language;
//		
//		
//		
//		// Get a database object
//		$db		= JFactory::getDbo();
//		$db->insertObject('#__autofbook_posted', $data, id);
//		
//		return;
//	}
	
	/**
	 * This method deletes the specified post
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param object	&$article	content object.
	 * 
	 * @return boolean	pinged
	 */
//	function _deleteArticlePost($article){
//		
//		//Variables used for changes since 1.5
//		$sectionCat;	//Used for sections (1.5) or categories (1.6+)
//		$query;			//Used since joomla 1.6+ handles queries different from 1.5
//		
//		// Get a database object
//		$db		=& JFactory::getDbo();
//		
//		if(version_compare(JVERSION,'1.7.0','ge')) {
//			//Delete the article with id
//			$query	= $db->getQuery(true);
//			$query->delete();
//			$query->from('#__autofbook_posted');
//			//$query->where('itemlink='.$db->Quote($this->_createUrl($article)));
//			$query->where('article_id=' . $db->Quote($article->id));
//		
//		} elseif(version_compare(JVERSION,'1.6.0','ge')) {
//			//Delete the article with id
//			$query	= $db->getQuery(true);
//			$query->delete();
//			$query->from('#__autofbook_posted');
//			//$query->where('itemlink='.$db->Quote($this->_createUrl($article)));
//			$query->where('article_id=' . $db->Quote($article->id));
//		
//		}  elseif(version_compare(JVERSION,'1.5.0','ge')) {
//			$sectionCat = $article->sectionid;
//			$query = 'DELETE '.
//						' FROM '.$db->nameQuote('#__autofbook_posted').
//						' WHERE '.$db->nameQuote('article_id').'='.$article->id;
//		}
//
//		$db->setQuery($query);
//		$result = $db->loadObject();
//		
//		return ;
//	}
	
	
	/**
	 * This method takes care of messagees to display.
	 * 
	 * @access private
	 * 
	 * @param string	$message		the message to display
	 * @param array		$messageType	type of message to display. "error" for errormessages, otherwise blank.
	 * 
	 * @return  void
	 */
//	function _displayMessage($msg, $messageType = "")
//	{
//		
//		//$lang = JFactory::getLanguage();
//		//$lang->load('plg_content_autofbook');
//		
//		//Check if messages is turned on, otherwise return
//		if($this->params->def('pingmessages')==0 || !$this->inBackend){
//			return;
//		}else{
//			JFactory::getApplication()->enqueueMessage( JText::_('PLG_SYSTEM_AUTOFBOOK_HEADER')." ".$msg, $messageType);
//		}
//		
//	}

/**
	 * This method Updates the facebook status
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param string	$message	The message to tweet
	 * 
	 * @return boolean	True if all went well
	 */
//	function _updateFacebookStatus($itemtitle, $itemlink, $itemdesc=""){
//		global $mainframe;
//		$resultArray['http_code'];
//		
//		$redirectURI=JURI::root();
//
//	
//		
//		//FACEBOOK App details from our settings 
//		$app_id = $this->params->def('fb_app_id');
//		$app_secret = $this->params->def('fb_app_secret');
//		$access_token = $this->params->def('auth_token');
//		$mymessage = $itemtitle;
//		
//
//		$access_token_url = "https://graph.facebook.com/me/accounts";
//		$fbpage = ( $this->params->def('fb_page_id') == "" )? "":$this->params->def('fb_page_id')."/";
//		$graphfeed_url = "https://graph.facebook.com/".$fbpage."feed";
//
//		//If we are updating a fan page, we need to get an admin access_token for this specific page,
//		// if this is set in the settings. Otherwise use the existing one
//		if($fbpage && $this->params->def('fb_post_as_admin') ){
//			$temp_token = $this->_getAdminTokenForPage($this->params->def('fb_page_id'));
//			
//			//Check if we managed to get a token 
//			if ( $temp_token ) {
//				$access_token = $temp_token;
//				$this->_displayMessage(JText::_("PLG_SYSTEM_AUTOFBOOK_POST_ADMIN_TOKEN_FOUND"));
//			}else{
//				$this->_displayMessage(JText::_("PLG_SYSTEM_AUTOFBOOK_POST_ADMIN_TOKEN_NOT_FOUND"));
//			}
//		}	  
//
//
//		/*
//		 * Time to post the message
//		 */
//		$message_postfields = array(
//			'access_token' => $access_token, 
//			'method' => 'post'
//			);
//		
//		switch ($this->params->def('fb_post_type')) {
//			case '0':
//				$message_postfields['link'] = $itemlink;
//				$message_postfields['message'] = $itemtitle;
//				$message_postfields['name'] = $itemtitle;
//				$message_postfields['description'] = $itemdesc;
//				break;
//			case '1':
//				$statusmessage = $itemtitle."\n".$itemlink;
//				$message_postfields['message'] = $statusmessage;
//				//$message_postfields['link'] = $itemlink;
//				//$message_postfields['description'] = $itemdesc;
//				break;
//				
//			
//			default:
//				$message_postfields['link'] = $itemlink;
//				$message_postfields['name'] = $itemtitle;
//				break;
//		}
//		
//		if ( ! $post_results = $this->_communicateWithFB($graphfeed_url, $message_postfields) ){
//			$this->_displayMessage(JText::_("PLG_SYSTEM_AUTOFBOOK_POST_FAILED"), 'error');
//			return false;
//		} 
//		return true;
//
//		/*********************************
//		 * STEP 1
//		 * Get the Auth Token
//		 * grant_type
//		**********************************/
//		
//		 //Verified
//		 /*
//		$postfields = array(
//				'grant_type'	=> "manage_pages",
//				'client_id' => $app_id,
//				'client_secret' => $app_secret,
//				'access_token' => $access_token
//		);
//		*/
//		 /*
//		$ch = curl_init(); 
//		//Set some curl options
//		curl_setopt($ch, CURLOPT_URL,$access_token_url); 	 //The auth page to visit
//		curl_setopt($ch, CURLOPT_POST, 1); 
//		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields)); //Build a query from the postarray
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		$result = curl_exec ($ch);							//Go get the page and put the result in a variable
//		$resultArray = curl_getinfo($ch); 
//		curl_close ($ch);									//Close the connection for now
//		*/
//		
//		
//		//$token = $this->_communicateWithFB($access_token_url, $postfields);								//Retrieve the token from the results we just got
//		//$this->_displayMessage($token);
//		
//		//$tokenArray = explode("=", $token['raw_result']);
//		//$this->_displayMessage($tokenArray[1]);
//		/*************************************
//		 * STEP 2
//		 * Login to Facebook and update status
//		**************************************/
//		 /*
//		$postfields = array(
//				'client_id' => $app_id,
//				'access_token'	=> $tokenArray[1],
//				'message' => $mymessage
//			);
//		  */
//		$postfields = array(
//				'client_id' => $app_id,
//				'access_token'	=> $this->params->def('auth_token'),
//				'message' => $mymessage
//			);
//		 
//		 $results = $this->_communicateWithFB($graphfeed_url, $postfields);
//		 
//		 if ($results['http_code'] != '200') {
//			$this->_displayMessage(JText::_("PLG_SYSTEM_AUTOFBOOK_LOGIN_FAILED").$results['http_code'], "error");
//			return FALSE;
//		 }
//		 return TRUE;
//		 
//		 //Trick the server if it asks for a referrer
//		 /*
//		$ch = curl_init(); 
//		 
//		curl_setopt($ch, CURLOPT_URL,$graphfeed_url);
//		curl_setopt($ch, CURLOPT_POST, 1); 
//		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields)); //Build a query from the postarray
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		 
//		$result = curl_exec ($ch);
//		$resultArray = curl_getinfo($ch);
//		
//		 
//		curl_close ($ch); 
//		if($resultArray['http_code'] != '200'){
//			$this->_displayMessage("Failed to log in", "error");
//			return FALSE;
//		}
//		
//		return TRUE;
//		*/
//	}


/**
	 * Checks if a update should be delayed
	 * 
	 * @access private
	 * @author Daniel
	 * 
	 * @param object	&$article	content object.
	 * 
	 * @return boolean	true if article should be delayed
	 */
//	function _updateShouldBeDelayed($article){
//        
//		//Set delaysettings
//		//$delay = 1;
//		$delayInMinutes = ( !is_numeric($this->params->def('delay')) || $this->params->def('delay')<2 )?3:$this->params->def('delay');
//		
// 	
//			
//		//Variables used for changes since 1.5
//		$sectionCat;	//Used for sections (1.5) or categories (1.6+)
//		$query;			//Used since joomla 1.6+ handles queries different from 1.5
//		
//		// Get a database object
//		$db		= JFactory::getDbo();
//		
//		if(version_compare(JVERSION,'1.7.0','ge')) {
//			//Check if the current link has already been pinged
//			$query	= $db->getQuery(true);
//			//$date=JFactory::getDate();
//			//$date->modify("-".$delayInMinutes." minutes");
//			$currentDate=date("Y-m-d H:i:s");
//			//Check if the current link has already been pinged within 5 minutes
//			$query->select('id');
//			$query->from('#__autofbook_posted');
//			//$query->where('date >'.$db->Quote($date));
//			$query->where("date > SUBTIME('".$currentDate."','0 0:".$delayInMinutes.":0.0')");
//		
//		} elseif(version_compare(JVERSION,'1.6.0','ge')) {
//			//Check if the current link has already been pinged
//			$query	= $db->getQuery(true);
//			//$date=JFactory::getDate();
//			//$date->modify("-".$delayInMinutes." minutes");
//			
//			$currentDate=date("Y-m-d H:i:s");
//			//Check if the current link has already been pinged within 5 minutes
//			$query->select('id');
//			$query->from('#__autofbook_posted');
//			//$query->where('date >'.$db->Quote($date));
//			$query->where("date > SUBTIME('".$currentDate."','0 0:".$delayInMinutes.":0.0')");
//		}  elseif(version_compare(JVERSION,'1.5.0','ge')) {
//			$currentDate=date("Y-m-d H:i:s");
//			$query = "SELECT "
//						.$db->nameQuote('id').
//						" FROM ".$db->nameQuote('#__autofbook_posted').
//						" WHERE date > SUBTIME('".$currentDate."','0 0:".$delayInMinutes.":0.0')";
//			//$this->_displayMessage($query);
//		}
//		
//		$db->setQuery($query);
//		$result = $db->loadObject();
//		
//		//If the article is already pinged, return true otherwise false
//		if($result){
//		$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_DELAY'));
//			return TRUE;
//		}
//		return FALSE;
//	}
	/**
	 * This method is used by Joomla 1.5 to create tables
	 * 
	 * @access private
	 * 
	 * 
	 * @return  boolean		success?
	 */
//	function _createTables()
//	{
//		// Get a database object
//		$db		=& JFactory::getDbo();
//		$queryDrop = "DROP TABLE IF EXISTS `#__autofbook_posted`";
//		
//		$queryCreate = "CREATE TABLE `#__autofbook_posted` (
//  			`id` int(11) NOT NULL auto_increment,
//  			`article_id` int(11) NOT NULL default 0,
//  			`cat_id` int(11) NOT NULL default 0,
//		  	`articlelink` varchar(255) NOT NULL default '',
//  			`date` datetime NOT NULL default '0000-00-00 00:00:00',
//  			`language` char(7) NOT NULL default '',
//  			PRIMARY KEY  (`id`)
//			) DEFAULT CHARSET=utf8;";
//		//$query	= $db->getQuery(true);
//
//		//$query->select('id');
//		//$query->from('#__blogpingpro_pinged');
//		//$query->where('article_id=' . $db->Quote($article->id). ' AND cat_id='.$db->Quote($sectionCat));
//
//		$db->setQuery($queryDrop);
//		$result = $db->query();
//		$db->setQuery($queryCreate);
//		$result = $db->query();
//		
//		//If the article is already pinged, return true otherwise false
//		if($result){
//			return TRUE;
//		}
//		return FALSE;
//	}
	/**
	 * This method is used by Joomla 1.5 to check if a table is installed
	 * 
	 * @access private
	 * 
	 * @return  boolean		success?
	 */
//	function _tableExists()
//	{
//		// Get a database object
//		$db		=& JFactory::getDbo();
//		$query = "DESC `#__autofbook_posted`";
//
//		$db->setQuery($query);
//		$result = $db->query();
//		
//		//If the article is already pinged, return true otherwise false
//		if($result){
//			return TRUE;
//		}
//		return FALSE;
//	}
	/**
	 * This method handles FB communication
	 * 
	 * @access private
	 * @param  string		$url	Page URL incl https://
	 * @param 	array 		$postfields		All postfields as array
	 * @return  array		result
	 */
//	function _communicateWithFB($url, $postfields)
//	{
//		 //Check if curl exists
//		 if ( !function_exists(curl_init) ) {
//			$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_CURL_FAILED'), 'error'); 
//		 }
//		 
//		 		
//		$ch = curl_init(); 
//		curl_setopt($ch, CURLOPT_URL,$url); 	 //The auth page to visit
//		curl_setopt($ch, CURLOPT_POST, 1); 
//		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields)); //Build a query from the postarray
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//		$the_results = curl_exec ($ch);							//Go get the page and put the result in a variable
//		$resultArray = curl_getinfo($ch); 
//		curl_close ($ch);	//Close the connection for now
//		
//		$this->_displayMessage($the_results);
//		
//		if (!$resultArray['http_code'] == "200") { 
//   			$this->_displayMessage(JText::_("PLG_SYSTEM_AUTOFBOOK_COMMUNICATION_FAILED"), 'error' );
//			$this->_displayMessage($resultArray['http_code'], 'error');
//			$this->_displayMessage($the_results, 'error');
//			//$this->_displayMessage($url."?".http_build_query($postfields));
//			return false;
//		}
//				
//		return $the_results;
//		
//		//Deprecated functions
//		$the_results = @file_get_contents($url."?".http_build_query($postfields));
//		if (!strpos($http_response_header[0], "200")) { 
//   			$this->_displayMessage(JText::_("failed to communicate with FB."));
//			$this->_displayMessage($http_response_header[0]);
//			$this->_displayMessage($the_results);
//			//$this->_displayMessage($url."?".http_build_query($postfields));
//			return false;
//		}
//		/*
//		if($the_results === FALSE){
//			$this->_displayMessage("failed to communicate with FB.");
//			$this->_displayMessage("xx ".$the_results);
//			return false;
//		}
//		*/
//		return $the_results;
//	}
	
	/**
	 * This method retrieves the token for a specific page
	 * 
	 * @access private
	 * @param  integer		$page_id
	 * @return  string		token
	 */
//	function _getAdminTokenForPage($fb_page_id)
//	{
//		//Connect to this page with the access_token.
//		$access_token_url = "https://graph.facebook.com/me/accounts";
//		$postfields = array(
//				'access_token' => $this->params->def('auth_token'),
//				'grant_type' => 'manage_pages'
//		);
//		
//		$the_results = @file_get_contents($access_token_url."?".http_build_query($postfields));
//		/*
//		if (!strpos($http_response_header[0], "200")) { 
//   			$this->_displayMessage("failed to communicate with FB.");
//			$this->_displayMessage($http_response_header[0]);
//			$this->_displayMessage($the_results);
//			//$this->_displayMessage($url."?".http_build_query($postfields));
//			return false;
//		}
//		*/
//		if($the_results==FALSE){
//			return FALSE;
//		}
//		/*
//		//$the_results = $this->_communicateWithFB($access_token_url, $postfields);
//		$the_results = $this->_communicateWithFB($access_token_url, $postfields);
//		if( !$the_results ){
//			$this->_displayMessage("xx failed to get new token");
//			$this->_displayMessage("xx ".$the_results);
//			return false;
//		}
//		*/
//		
//		//return false if any error occured
//		/*
//		if ($the_results['http_code'] != 200) {
//			$this->_displayMessage("xx failed to get new token, code: ".$the_results['http_code']);
//			$this->_displayMessage("xx".$the_results['raw_result']);
//			return false;
//		} 
//		*/
//		
//		//Check if server supports JSON
//		if( !function_exists('json_decode') ){
//			$this->_displayMessage(JText::_("PLG_SYSTEM_AUTOFBOOK_JSON_FAILED"), 'error');
//			return false;
//		}
//		
//		//Convert the returned JSON to an array of objects
//		$fb_return_obj = json_decode($the_results);
//		
//		//Iterate the array in search of the page id
//		for ($i=0; $i < count($fb_return_obj->data) ; $i++) {
//			if( $fb_return_obj->data[$i]->id == $fb_page_id ){
//				//Page ID found, return the access_token	
//				$this->_displayMessage(JText::_('PLG_SYSTEM_AUTOFBOOK_POST_ADMIN_TOKEN_FOR_PAGE_FOUND').$fb_return_obj->data[$i]->name);
//				return $fb_return_obj->data[$i]->access_token;
//			}
//		}
//		
//		//Nothing found, return false
//		return false;
//	
//	}
	
} // END CLASS

