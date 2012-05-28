<?php
/**
 * A Class for communication with Facebook
 *
 * @version $Id: fbookClass.php 28 2012-05-26 15:49:51Z webbochsant@gmail.com $
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
class FBookClass {
    private $fbAppID;
    private $fbAppSecret;
    private $fbOauthAccessToken;
    private $fbOauthPageAccessToken;
    private $fbOauthCode;
    protected $config;
    private $error = false;
    private $info = false;
    private $debugInfo = false;
    private $notice = false;
    const HTTP_STATUS_OK = '200';
    const ERROR_RETURNURL_NOT_SPECIFIED = '10';
    const ERROR_AUTHTOKENURL_NOT_SPECIFIED = '11';
    const ERROR_URL_NOT_VALID = '12';
    const ERROR_POST_FAIL = '13';
    const ERROR_COMMUNICATION_FAULT = '14';
    const ERROR_OAUTH_EXCEPTION = '50';
    const ERROR_OAUTH_OTHER = '55';

    function __construct($fbAppID, $fbAppSecret, $config="") {
        $this->fbAppID = $fbAppID;
        $this->fbAppSecret = $fbAppSecret;
        $this->config = 
            array(
                'redirectURI'          =>  '',
                'fbOauthToken'         =>  '',
                'fbPageID'              =>  '',
                'authTokenURL'          =>  'https://www.facebook.com/dialog/oauth',
                'authScope'             =>  'publish_stream,share_item,offline_access,manage_pages',
                'authResponseType'      =>  'response_type=token',
                'accessTokenURL'        =>  'https://graph.facebook.com/me/accounts',
                'graphAccessToken'      =>  'https://graph.facebook.com/oauth/access_token',
                'graphFeedURL'          =>  'https://graph.facebook.com/me',
                'graphURL'              =>  'https://graph.facebook.com/',
                'curlUserAgent'         =>  'oauthFBClass - www.stilero.com',
                'curlConnectTimeout'    =>  20,
                'curlTimeout'           =>  20,
                'curlReturnTransf'      =>  true,
                'curlSSLVerifyPeer'     =>  false,
                'curlFollowLocation'    =>  false,
                'curlProxy'             =>  false,
                'curlProxyPassword'     =>  false,
                'curlEncoding'          =>  false,
                'curlHeader'            =>  false,
                'curlHeaderOut'         =>  true,
                'postToPageAsAdmin'     =>  true,     
                'debug'                 =>  false,
                'eol'                   =>  "<br /><br />"
            );
        if(is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $this->fetchCode();
    } 
    
    protected function fetchCode(){
        if(isset($_GET['code']) && $_GET['state'] == $this->getEncryptedCSFRState()){
            $code = $this->cleanRequest($_GET['code']);
            $this->setOauthCode($code);
        }
    }
    
    public function cleanRequest($request){
        $cleanedRequest = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $request);
        $cleanedRequest = ltrim($cleanedRequest, '.');
        return $cleanedRequest;
    }
    
    public function postLinkToFB($link, $name=""){
        $this->prepareToken();
        if($this->hasErrorOccured()){
            return;
            $this->setDebugInfo('error before post link');
        }
        $postvars = array( 
            'access_token'  =>  $this->fbOauthAccessToken,
            'method'        =>  'post',
            'link'          =>  $link,
            'name'          =>  $name
        );
        $fbPageID = ( $this->config['fbPageID'] == "" )? "" : $this->config['fbPageID']."/";
        $graphURL = $this->config['graphURL'].$fbPageID."feed";
        $this->setDebugInfo('Posting Link: '.$graphURL.'?'.  http_build_query($postvars));
        $response = $this->query($graphURL, $postvars);
        $this->handleResponse($response);
        return !$this->hasErrorOccured();
        //}
    }
    
    public function postStatusMessageToFB($message){
        $this->prepareToken();
        if( $this->hasErrorOccured()) {
            return;
        }
        $postvars = array( 
            'access_token'  =>  $this->fbOauthAccessToken,
            'method'        =>  'post',
            'message'       =>  $message,
        );
        $fbPageID = ( $this->config['fbPageID'] == "" )? "" : $this->config['fbPageID']."/";
        $graphURL = $this->config['graphURL'].$fbPageID."feed";
        $response = $this->query($graphURL, $postvars);
        $this->handleResponse($response);
        return !$this->hasErrorOccured();
    }
    
    public function prepareToken(){
        if($this->hasErrorOccured()){
            return;
        }
        $this->setDebugInfo('Start in prepareToken.');
        $this->authCodeToAuthToken();
        if($this->hasErrorOccured()){ 
            return;
        }else{
            $this->fbOauthCode = '';
        }
        
        $accesstoken = $this->getOauthAccessToken();
        if( isset($accesstoken) ){
            $this->setDebugInfo('calling tryGraph from prepareToken.');
            $this->tryGraphQuery();
        }else{
            $this->setDebugInfo('No token in prepareToken, request Permissions.');
            $this->requestPermissionsForApp();
            return;
        }
        if( $this->getErrorCode() == self::ERROR_COMMUNICATION_FAULT){
            return false;
        }
        if( $this->getErrorCode() == self::ERROR_OAUTH_EXCEPTION ) {
            $this->setDebugInfo('Oauth exception in prepareToken, request Permissions.');
            $this->resetErrors();
            $response = $this->extendOAuthTokenExpireTime();
            $this->setError(self::ERROR_OAUTH_EXCEPTION, 'extendOauthToken');
            if($this->hasErrorOccured()){
                $this->setDebugInfo('Failed extending token in prepareToken');
                $this->requestPermissionsForApp();
            }
            if($this->hasErrorOccured()){
                $this->setDebugInfo('Failed requesting permissions in prepareToken');
                return;
            }
            $this->setOauthAccessToken($this->findTokenInResponse($response));
        }
        if( $this->getErrorCode() == self::ERROR_OAUTH_EXCEPTION ) {
            $this->resetErrors();
            $this->validateToken();
        }
        if( $this->getErrorCode() == self::ERROR_OAUTH_EXCEPTION ) {
            $this->resetErrors();
            $this->requestPermissionsForApp();
            return;
        }
        if( $this->getErrorCode() == self::ERROR_COMMUNICATION_FAULT){
            return false;
        }
        $this->fetchPageAdminToken();
    }
    
    private function fetchPageAdminToken(){
        $userToken = $this->fbOauthAccessToken;
        if($this->getFBPageID() != "" && $this->willPostToPageAsAdmin()===TRUE){
            $this->setDebugInfo('get a page token in fetchPageAdminToken.');
            $response = $this->requestAdminTokenForPage();
            if($this->hasErrorOccured()) return;
            $pageToken = $this->findPageAdminTokenInJsonResponse($response);
            $this->setOauthAccessToken($pageToken);
        }
        if(!$this->tryGraphQuery(true)){
            $this->setOauthAccessToken($userToken);
            $this->setNotice('Could not get admin rights for page '.$this->getFBPageID().', posting to personal wall.');
            $this->setFBPageId('');
        }
    }
    
    private function authCodeToAuthToken(){
        if(isset($this->fbOauthCode)){
            $this->setDebugInfo('Inside authCodeToAuthToken');
            $response = $this->requestAccessTokenForApp();
            if( $this->hasErrorOccured() && $this->getErrorMessage() == 'Error validating verification code.'){
                $this->requestPermissionsForApp();
                return;
            }  elseif ($this->hasErrorOccured()) {
                $this->setDebugInfo('failed requestTokenForApp');
               return;
            }
            $this->setOauthAccessToken($this->findTokenInResponse($response));
        }
        $this->setDebugInfo('requestTokenForApp in authCodeToAuthToken. token: '.$this->getOauthAccessToken());
        return $this->getOauthAccessToken();
    }

    private function tryGraphQuery($isPageQuery=false){
        $header = $this->buildHTTPHeader();
        $graphURL = $this->config['graphFeedURL'];
        $token = $this->getOauthAccessToken($isPageQuery);
        $postVars = array(
            'access_token' =>  $token,
        );
        $tokenURL = $graphURL ."?". http_build_query($postVars);
        $response = $this->query($tokenURL, $postVars, FALSE, $header);
        $page = $isPageQuery ? ' as page: ' : ': ';
        $this->setDebugInfo('trying graph query'.$page.$tokenURL);
        return !$this->hasErrorOccured();
    }
    
    public function findTokenInResponse($response){
        parse_str($response, $responses);
        return isset($responses['access_token']) ? $responses['access_token'] : false;
    }
    
    protected function findPageAdminTokenInJsonResponse($jsonResponse) {
        $JsonResponse = json_decode($jsonResponse);
        if(!isset($JsonResponse->data)){
            return false;
        }
        for ( $i=0 ; $i < count($JsonResponse->data) ; $i++) {
            if( $JsonResponse->data[$i]->id == $this->config['fbPageID'] ){
                return $JsonResponse->data[$i]->access_token;
            }
        }
        return false;
    }
    
    private function buildHTTPHeader(){
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,"; 
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; 
        $header[] = "Cache-Control: max-age=0"; 
        $header[] = "Connection: keep-alive"; 
        $header[] = "Keep-Alive: 300"; 
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
        $header[] = "Accept-Language: en-us,en;q=0.5"; 
        $header[] = "Pragma: ";  
        return $header;
    }
    
    private function requestAdminTokenForPage(){
        $header = $this->buildHTTPHeader();
        $graphURL = $this->config['accessTokenURL'];
        $postVars = array(
            'access_token' =>  $this->fbOauthAccessToken,
            'grant_type' => 'manage_pages'
        );
        $tokenURL = $graphURL ."?". http_build_query($postVars);
        $response = $this->query($tokenURL, $postVars, FALSE, $header);
        return $response;

    }
    
    protected function requestPermissionsForApp(){
        $dialogURL = $this->getOAuthDialogURL();
        print "<script> top.location.href='".$dialogURL."'</script>";
        return;
    }
    
    private function isURL($url){
        $parsedURL = parse_url($url);
        if( !isset($parsedURL['scheme']) ){
            return false;
        }
        if( !isset($parsedURL['host']) ){
            return false;
        }
        if( $parsedURL['scheme'] !='http' && $parsedURL['scheme'] != 'https' ){
            return false;
        }
        return true;
    }

    private function extendOAuthTokenExpireTime(){
        $postVars = array(
            'client_id'     =>  $this->fbAppID,
            'client_secret' =>  $this->fbAppSecret,
            'grant_type'    =>  'fb_exchange_token',
            'fb_exchange_token' =>  $this->fbOauthAccessToken
        );
        $this->setDebugInfo($this->config['graphAccessToken'].'?'.http_build_query($postVars) );
        return $this->query($this->config['graphAccessToken'], $postVars);
    }
    
    private function validateToken(){
        $postVars = array(
            'client_id'     =>  $this->getAppID(),
            'client_secret' =>  $this->fbAppSecret,
            'redirect_uri'    =>  $this->config['redirectURI'],
            'code' =>  $this->fbOauthAccessToken
        );
        return $this->query($this->config['graphAccessToken'], $postVars);
    }
    
    public function getOAuthDialogURL(){
        $postVars = array(
            'client_id'     =>  $this->fbAppID,
            'redirect_uri'    =>  $this->config['redirectURI'],
            'scope' =>  $this->config['authScope'],
            'state' => $this->getEncryptedCSFRState()
        );
        return $tokenURL = $this->config['authTokenURL'] ."?". http_build_query($postVars);
    }
    
    public function requestAccessTokenForApp(){
        $postVars = array(
            'client_id'     =>  $this->fbAppID,
            'client_secret' =>  $this->fbAppSecret,
            'redirect_uri'    =>  $this->config['redirectURI'],
            'code' =>  $this->fbOauthCode
        );
        $this->setDebugInfo('requestTokenForApp: '.$this->config['graphAccessToken'].'?'.http_build_query($postVars));
        return $this->query($this->config['graphAccessToken'], $postVars);
    }
    
    private function query($fbURL, $postVars, $post=TRUE, $header = null){
        $ch = curl_init(); 
         curl_setopt_array($ch, array(
            CURLOPT_USERAGENT       =>  $this->config['curlUserAgent'],
            CURLOPT_CONNECTTIMEOUT  =>  $this->config['curlConnectTimeout'],
            CURLOPT_TIMEOUT         =>  $this->config['curlTimeout'],
            CURLOPT_RETURNTRANSFER  =>  $this->config['curlReturnTransf'],
            CURLOPT_SSL_VERIFYPEER  =>  $this->config['curlSSLVerifyPeer'],
            CURLOPT_FOLLOWLOCATION  =>  $this->config['curlFollowLocation'],
            CURLOPT_PROXY           =>  $this->config['curlProxy'],
            CURLOPT_ENCODING        =>  $this->config['curlEncoding'],
            CURLOPT_URL             =>  $fbURL,
            //CURLOPT_POST            =>  $post,
            CURLOPT_HEADER          =>  $this->config['curlHeader'],
            CURLINFO_HEADER_OUT     =>  $this->config['curlHeaderOut'],
        ));
        if($post){
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postVars));
        }
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if ($this->config['curlProxyPassword'] !== false) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config['curl_proxyuserpwd']);
        } 
        $fbResponse = curl_exec ($ch);
        $fbResponseArray = curl_getinfo($ch); 
        curl_close ($ch);
        if($fbResponseArray['http_code'] == 0){
            $this->setError(self::ERROR_COMMUNICATION_FAULT, 'Communication error');
            return false;
        }else if ($fbResponseArray['http_code'] != self::HTTP_STATUS_OK) {
            $this->handleResponse($fbResponse);
            return false;
        }
        return $fbResponse;
    }
    
    private function handleResponse($response){
        if(!isset($response)){
            return;
        }
        $ResponseJSON = json_decode($response);
        if(isset($ResponseJSON->error)){
            if($ResponseJSON->error->type == 'OAuthException'){
                $this->setError(self::ERROR_OAUTH_EXCEPTION, $ResponseJSON->error->message);
            }else{
                $this->setError(self::ERROR_OAUTH_OTHER);
            }
        }else if (isset($ResponseJSON->id)){
            $this->setInfo(self::HTTP_STATUS_OK); 
        }
    }
    
    public function outputVar($var, $halt = true){
        print "<pre>";
        if(is_array($var) || is_object($var)){
            print_r($var);
        }else if ($var){
            print($var);
        }
        print"</pre>";
        if($halt) exit;
    }
    
    public function setRedirectURI($url){
        if(!$this->isURL($url)) {
            $this->setError(self::ERROR_URL_NOT_VALID);
            return false;
        }
        $this->config['redirectURI'] = $url;
    }
    
    public function setOauthCode($code){
        if($code !='') $this->fbOauthCode = $code;
    }
    
    public function setOauthAccessToken($token, $isPageQuery = false){
        if($token ==''){
            return;
        }
        $this->fbOauthAccessToken = $token;
        
    }
    
    public function getOauthAccessToken($isPageQuery = false){
        //$token = (isset($this->fbOauthPageAccessToken)) ? $this->fbOauthPageAccessToken : $this->fbOauthAccessToken;
        return $this->fbOauthAccessToken;
    }
    
    public function getOauthCode(){
        return $this->fbOauthCode;
    }
    
    public function getAppID(){
        return $this->fbAppID;
    }
    
    public function setAppID($appID){
        $this->fbAppID = $appID;
    }
    
    public function getAppSecret(){
        return $this->fbAppSecret;
    }
    
    public function setAppSecret($appSecret){
        $this->fbAppSecret = $appSecret;
    }
    
    public function getFBPageID(){
        return $this->config['fbPageID'];
    }
    
    public function setError($errorCode, $errorMessage=""){
        $this->error['code'] = $errorCode;
        if($errorMessage!=""){
            $this->error['message'] = $errorMessage;
        }
    }
    
    public function getError(){
        return $this->error;
    }
    
    public function getErrorCode(){
        return (isset($this->error['code']) ) ? $this->error['code'] : false;
    }
    
    public function hasErrorOccured(){
        if(!$this->error){
            return false;
        }else{
            return true;
        }
    }
    public function getErrorMessage(){
        return ( isset($this->error['message']) )? $this->error['message'] : false;
    }
    
    public function getEncryptedCSFRState(){
        $key = $this->fbAppID;
        $string = $this->fbAppSecret;
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
        $encrypted = str_replace(array('+','/','='),array('-','_',''),$encrypted);        
        return $encrypted;
    }
    
    public function resetErrors(){
        $this->error = false;
    }
    
    public function setDebugInfo($infomessage){
        $this->debugInfo[] = $infomessage;
    }
    
    public function setInfo($infomessage){
        $this->info = $infomessage;
    }
    
    public function getInfo(){
        return $this->info;
    }
    
    public function getDebugInfo(){
        return $this->debugInfo;
    }
    
    public function setNotice($message){
        $this->notice[] = $message;
    }
    
    public function getNotice(){
        return $this->notice;
    }
    
    public function setFBPageId($pageID){
        $this->config['fbPageID'] = $pageID;
    }
    
    public function willPostToPageAsAdmin(){
        return $this->config['postToPageAsAdmin'];
    }

    public function setPostToPageAsAdmin($trueOrFalse){
        if($trueOrFalse === FALSE ){
            $this->config['postToPageAsAdmin'] = FALSE;
        }else{
            $this->config['postToPageAsAdmin'] = TRUE;
        }
    }
}
?>