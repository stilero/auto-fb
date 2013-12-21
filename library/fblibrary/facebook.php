<?php
/**
 * Facebook class as a starter point
 * 
 * POSTS TO PERSONAL WALL
 * To post a status message to your personal wall:
 * 1. Retrieve an access token and authorize your app:
 *      $Facebook = new StileroFBFacebook($appID, $appSecret, $redirectURI);
 *      $Facebook->init();
 *      $token = $Facebook->getToken();
 * 2. Store the token for future calls, otherwise you will need to reauthorise
 * 3. Do the API Calls with your token
 *      $Facebook = new StileroFBFacebook($appID, $appSecret, $redirectURI);
 *      $Facebook->setAccessTokenFromToken($token);
 *      $Facebook->init();
 *      $response = $Facebook->Feed->postLink('http://www.streetpeople.se');
 *      $debug = StileroFBResponse::handle($response);
 *      var_dump($debug);
 *      $updatedToken = $Facebook->getToken();
 * 4. Store the updated token and use for future calls. 
 * 
 * POSTS TO PAGE WALL
 * To post a photo to your page wall:
 * 1. Retrieve an access token and authorize your app (only done once):
 *      $Facebook = new StileroFBFacebook($appID, $appSecret, $redirectURI);
 *      $Facebook->init();
 *      $token = $Facebook->getToken();
 * 2. Store the token for future calls, otherwise you will need to reauthorise
 * 3. Do the API Calls with your token
 *      $Facebook = new StileroFBFacebook($appID, $appSecret, $redirectURI);
 *      $Facebook->setAccessTokenFromToken($token);
 *      $Facebook->init();
 *      $pageToken = $Facebook->User->getTokenForPageWithId($pageID);
 *      $Facebook->Feed->setToken($pageToken);
 *      $response = $Facebook->Photos->publishFromUrl('http://ilovephoto.se/images/portfolio/bestphotos/portfolio-photography-corporate-12-13120909.jpg');
 *      $debug = StileroFBResponse::handle($response);
 *      var_dump($debug);
 *      $updatedToken = $Facebook->getToken();
 * 4. Store the updated token and use for future calls. 
 *
 * @version  1.0
 * @package Stilero
 * @subpackage class-oauth-fb
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-20 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroFBFacebook{
    public $Comments;
    public $Feed;
    public $Likes;
    public $Photos;
    public $User;
    protected $App;
    public $AccessToken;
    protected $redirectUri;
    protected $userId = 'me';   
    
    /**
     * The Controller/Wrapper for the entire Facebook class
     * @param string $appId The Facebook App ID received from developers.facebook.com
     * @param string $appSecret The Facebook App Secret received from developers.facebook.com
     * @param string $redirectUri The redirect url is typically the absolute url to the page where this script is run (http://www.mypage.com/index.php)
     */
    public function __construct($appId, $appSecret, $redirectUri) {
        $this->App = new StileroFBOauthApp($appId, $appSecret);
        $this->redirectUri = $redirectUri;
    }
    
    /**
     * Returns the Login Dialog URL for authorising apps at Facebook
     * @return string Login Dialog Url
     */
    protected function loginDialogUrl(){
        $Dialog = new StileroFBLoginDialog($this->App, $this->redirectUri);
        $csfrState = StileroOauthEncryption::EncryptedCSFRState($this->App->id, $this->App->secret);
        $responseType = StileroFBLoginDialogResponseType::CODE;
        $scope = StileroFBPermisisonsPagesGroupsUsers::permissionList();
        $url = $Dialog->url($csfrState, $responseType, $scope);
        return $url;
    }
    
    /**
     * Redirects the user to the FB LoginDialog by printing out a JScript.
     */
    protected function redirectToLoginDialog(){
        $url = $this->loginDialogUrl();
        print "<script> top.location.href='".$url."'</script>";
    }
    
    /**
     * The starting point for this api. In no AccessToken is set the user is redirected
     * to the Login Dialog. This method will also catch any access codes returned, 
     * and sets it to the AccessToken.
     * Don't forget to call the getAccessToken and save the token for future calls
     * to avoid the need of reauthorisation.
     * @param integer $userId The User/Wall/group id to send posts to
     */
    public function init(){
        if(!isset($this->AccessToken) && (!StileroFBOauthCode::hasCodeInGetRequest())){
            $this->redirectToLoginDialog();
        }else if(StileroFBOauthCode::hasCodeInGetRequest()){
            $Code = new StileroFBOauthCode();
            $Code->fetchCode();
            $AccessToken = new StileroFBOauthAccesstoken($this->App);
            $response = $AccessToken->getTokenFromCode($Code->code, $this->redirectUri);
            $AccessToken->tokenFromResponse($response);
            $this->setAccessTokenFromToken($AccessToken->token);
        }
        $this->renewToken();
        $this->Feed = new StileroFBEndpointFeed($this->AccessToken, $this->userId);
        $this->User = new StileroFBEndpointUser($this->AccessToken, $this->userId);
        $this->Photos = new StileroFBEndpointPhotos($this->AccessToken, $this->userId);
    }
    
    /**
     * Checks if a token will expire and extends it if not permanent
     */
    protected function renewToken(){
        if(!$this->AccessToken->willNeverExpire($this->AccessToken->token)){
            $this->AccessToken->extend();
        }
    }
    
    /**
     * Sets the user id
     * @param int $userId The User/Page/Group id
     */
    public function setUserId($userId){
        $this->userId = $userId;
    }
    
    /**
     * Takes a token and creates an AccessToken object for this class.
     * @param string $token token string
     */
    public function setAccessTokenFromToken($token){
        $AccessToken = new StileroFBOauthAccesstoken($this->App);
        $AccessToken->setToken($token);
        $this->AccessToken = $AccessToken;
    }
    
    /**
     * Returns the token of the AccessToken object
     * @return string token
     */
    public function getToken(){
        return $this->AccessToken->token;
    }
}
