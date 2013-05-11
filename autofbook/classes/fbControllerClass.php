<?php
/**
 * A Class for doing necessary checks before sharing to social services
 *
 * @version $Id: fbControllerClass.php 27 2012-02-22 15:49:51Z webbochsant@gmail.com $
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-22 Stilero AB
 * @license GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of AutoFBook
 * 
 * AutoFBook is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or 
 * (at your option) any later version.
 * 
 * AutoFBook is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with AutoFBook.  
 * If not, see <http://www.gnu.org/licenses/>.
 */

class fbControllerClass extends stlShareControllerClass{
    const HTTP_STATUS_FOUND = 302; //All ok but prefers other options
    const HTTP_STATUS_OK = 200; //Returned on all ok
    const HTTP_STATUS_FORBIDDEN = 403; //Returned from Twitter on duplicate tweets
    var $fbClass;
    
    function __construct($config) {
        parent::__construct($config);
        $this->config = array_merge(  
            array(
            'fbookClass'            =>      'JFBClass',
            'fbAppID'               =>      '',
            'fbAppSecret'           =>      '',
            'fbOauthAccessToken'    =>      '',
            'fbOauthCode'           =>      '',
            'shareLogTableName'     =>      '',
            'pluginLangPrefix'      =>      '',
            'categoriesToShare'     =>      '',
            'shareDelay'            =>      '',
            'articlesNewerThan'     =>      '',
            'debug'                 =>      false,
            'debugRedirectURI'      =>      ''
            ),
        $config
        );
    }
    
    public function shareLinkToFB() {
        
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if( $this->error != FALSE) {
            return false;
        }

        if($this->config['fbOauthAccessToken']!='' || $this->config['fbOauthCode'] != '' ){
            $this->saveLogToDB();
        }
        if(!$this->doShareLink()){
            $errorcode = $this->fbClass->getErrorCode();
            $errorMessage = $this->fbClass->getErrorMessage();
            $this->error['message'] = $this->config['pluginLangPrefix'].'ERRORCODE_'.$errorcode;
            $this->error['type'] = 'error';
            $this->deleteLogFromDB();
            return false;
        }
        if( $this->fbClass->getInfo() == self::HTTP_STATUS_OK ){
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.': all went well, save log to db' );
            return true;
        } else if($this->fbClass->getNotice() != FALSE){
            //if (JDEBUG) JError::raiseNotice( 0, $this->fbClass->getNotice() );
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." param: ".$this->params->toString() );
            $this->error['message'] = $this->config['pluginLangPrefix'].'ERRORCODE_'.$this->fbClass->getNotice();
            $this->error['type'] = 'notice';
            $this->deleteLogFromDB();
            return false;
        }else if($this->fbClass->getError() != FALSE){
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.': error occured' );
            $this->error['message'] = $this->config['pluginLangPrefix'].'ERRORCODE_'.$errorcode;
            $this->error['type'] = 'error';
            $this->deleteLogFromDB();
            return false;            
        }
        $this->deleteLogFromDB();
        return false;
    }
    
    private function doShareLink() {
        if( !$this->initializeClasses() ){
            $this->error['message'] = $this->config['pluginLangPrefix'].'FAILED_INIT_CLASSES';
            $this->error['type'] = 'error';
            return false;
        }
        $link = $this->articleObject->url;
        $name = $this->articleObject->title;
        $this->fbClass->postLinkToFB($link, $name);
        if($this->fbClass->hasErrorOccured()){
            return false;
        }else{
            return true;
        }
    }
        
    private function initializeClasses() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return;
        }
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.": passed error check" );
       $className = $this->config['fbookClass'];
       $this->fbClass = new $className($this->config['fbAppID'], $this->config['fbAppSecret']);
        $requestSignature = $this->getRequestSignature();
       $redirect_url = JURI::current().'?signature='.$this->getRequestSignature();
       $redirect_url = ($this->config['debug'])? $this->config['debugRedirectURI'] : $redirect_url;
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' redirect_url: '.$redirect_url );
       $this->fbClass->setRedirectURI($redirect_url);
       $this->fbClass->setFBPageId($this->config['fbPageID']);
       $this->fbClass->setOauthAccessToken($this->config['fbOauthAccessToken']);
       $this->fbClass->setOauthCode($this->config['fbOauthCode']);
       $this->fbClass->inBackend = $this->config['inBackend'];
       if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." redirect_uri:".$redirect_url );
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." fbAppID: ".$this->fbClass->getAppID() );
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." fbAppSecret: ".$this->fbClass->getAppSecret() );
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." OAuthToken: ".$this->fbClass->getOauthAccessToken() );
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__." OAuthCode: ".$this->fbClass->getOauthCode() );
        

       $debugmessage = ($this->fbClass->hasErrorOccured())?" error found":" no errors";
       if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.": ".$debugmessage );
       return !$this->fbClass->hasErrorOccured();
    }
    
    public function getRequestSignature(){
        $data = $this->config['fbAppID'];
        $key = $this->config['fbAppSecret'];
        $signature = rawurlencode(
                base64_encode(
                        hash_hmac( 'sha1', $data, $key, true )
                ) 
        );
        return $signature;
    }
    
    public function storeParams($params, $pluginElement){
       if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__);
        $tableName = '#__extensions';
        if($this->isJoomla15()){
            $tableName = '#__plugins';
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(TRUE);
        $query->update($tableName);
        $query->set('params'.'='.$db->quote($params->toString()));
        $query->where('element='.$db->quote($params->toString()));
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' SQL: '.$query);
        $db->setQuery($query);
        $db->query();
        
    }
    
    public function isServerSupportingRequiredFunctions(){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        if( ! function_exists( 'curl_init' ) || ! function_exists('file_get_contents') || ! function_exists('json_decode') ){
            $this->error['message'] = $this->config['pluginLangPrefix'].'NO_CURL_SUPPORT';
            $this->error['type'] = 'error';
            return FALSE;
        }
    }
    
    public function isServerSafeModeDisabled (){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if(ini_get('safe_mode')){
            $this->error['message'] = $this->config['pluginLangPrefix'].'SERVER_IN_SAFE_MODE';
            $this->error['type'] = 'error';
            return FALSE;
        }
    }

    public function isFBAppDetailsEntered() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        if( $this->config['fbAppID'] == "" || $this->config['fbAppSecret'] == ""){
            $this->error['message'] = $this->config['pluginLangPrefix'].'NOLOGINDETAILS';
            $this->error['type'] = 'error';
            return FALSE;
        }
    }

    public function setFBAppDetails($twitterUsername, $twitterPassword) {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->config['fbAppID'] = $twitterUsername;
        $this->config['fbAppSecret'] = $twitterPassword;
    }

}

