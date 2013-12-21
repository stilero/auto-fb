<?php
/**
 * Endpoint Class
 *
 * @version  1.0
 * @package Stilero
 * @subpackage class-oauth-fb
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-18 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroFBEndpointEndpoint extends StileroOauthCommunicator{
    
    protected static $_graph_url = 'https://graph.facebook.com/';
    protected $params = array();
    protected $requestUrl;
    protected $AccessToken;
    protected $userId = 'me';
    /**
     * Class for removing repeated code
     * @param type $url
     * @param type $postVars
     * @param type $config
     */
    public function __construct(StileroFBOauthAccesstoken $AccessToken) {
        parent::__construct();
        $this->AccessToken = $AccessToken;
    }
    
    /**
     * Publishes the request
     * @param string $requestType Use constant from Communicator class
     * @param boolean Set to true, to send the auth token with the call
     * @return string JSON Response
     */
    protected function sendRequest($requestType='', $useAuth=true){
        $requestUrl = $this->requestUrl;
        if($useAuth){
            $this->params['access_token'] = $this->AccessToken->token;
        }
        if($requestType == self::REQUEST_METHOD_GET){
            $requestUrl = $this->requestUrl ."?". http_build_query($this->params);
            $this->setCustomRequest(self::REQUEST_METHOD_GET);
        }else{
            $this->setPostVars($this->params);
        }
        //var_dump($requestUrl);
        //var_dump($this->params);
        $this->setUrl($requestUrl);
        $this->query();
        $response = $this->getResponse();
        return $response;
    }
    
    public function setToken($token){
        $this->AccessToken->token = $token;
    }
    
    public function setUserId($userId){
        $this->userId = $userId;
    }
}

