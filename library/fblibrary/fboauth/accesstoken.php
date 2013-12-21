<?php
/**
 * Access token class
 *
 * @version  1.0
 * @package Stilero
 * @subpackage class-oauth-fb
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-okt-03 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroFBOauthAccesstoken extends StileroOauthCommunicator{
    
    private static $_graph_url = 'https://graph.facebook.com/';
    public $token;
    private $_FBApp;
    private $_redirectUri;
    private $_oauthCode;
    
    
    public function __construct(StileroFBOauthApp $FbApp) {
        parent::__construct();
        $this->_FBApp = $FbApp;
    }
    
    /**
     * Exchanges an access code to an access token
     * @param string $oauthCode Code received from Login Dialog
     * @param string $redirectUri Redirect URI
     * @return string oAuth Token
     */
    public function getTokenFromCode($oauthCode, $redirectUri){
        $url = self::$_graph_url.'oauth/access_token?'.
                'client_id='.$this->_FBApp->key.
                '&redirect_uri='.$redirectUri.
                '&client_secret='.$this->_FBApp->secret.
                '&code='.$oauthCode;
        $this->setUrl($url);
        $this->setCustomRequest('GET');
        $this->query();
        $response = $this->getResponse();
        return $response;
        }
    
    /**
     * Extracts the token from the response
     * @param string $response Response
     * @return string Token
     */
    public function tokenFromResponse($response){
        $responses = explode('&', $response);
        $token = '';
        foreach ($responses as $resp) {
            $respParts = explode('=', $resp);
            $token = $respParts[1];
        }
        $this->token = $token;
    }
    
    /**
     * Extends the life of a token, and exchanges a short lived token with a
     * long term token.
     * @return string JSON Response
     */
    public function extend(){
        $url = self::$_graph_url.'oauth/access_token';
        $postVars = array(
            'client_id'     =>  $this->_FBApp->key,
            'client_secret' =>  $this->_FBApp->secret,
            'grant_type'    =>  'fb_exchange_token',
            'fb_exchange_token' =>  $this->token
        );
        $requestUrl = $url ."?". http_build_query($postVars);
        $this->setUrl($requestUrl);
        $this->setCustomRequest('GET');
        $this->query();
        $response = $this->getResponse();
        return $response;
    }
    
    /**
     * Get info about a token and debug for checking expiry
     * @param string $inputToken The token to check
     * @return string JSON Response
     */
    public function debug($inputToken){
        $url = self::$_graph_url.'debug_token';
        $postVars = array(
            'input_token' => $inputToken,
            'access_token' =>  $this->token
        );
        $requestUrl = $url ."?". http_build_query($postVars);
        $this->setUrl($requestUrl);
        $this->setCustomRequest('GET');
        $this->query();
        $response = $this->getResponse();
        return $response;
    }
    
    /**
     * Checks if a token is short term or long term. If the issued_at field is returned,
     * then the token is long term
     * @param string $inputToken The Token to check
     * @return boolean true if short term
     */
    public function isShortTerm($inputToken){
        $debug = json_decode($this->debug($inputToken));
        if(isset($debug->data->issued_at)){
            return false;
        }
        return true;
    }
    
    /**
     * Checks if a token is permanent, and will never expire
     * @param string $inputToken The Token to check
     * @return boolean true if permanent
     */
    public function willNeverExpire($inputToken){
        $debug = json_decode($this->debug($inputToken));
        if(isset($debug->data->expires_at) && ($debug->data->expires_at == 0)){
            return true;
        }
        return false;
    }
    
    /**
     * Sets the access token
     * @param string $token
     */
    public function setToken($token){
        $this->token = $token;
    }
}
