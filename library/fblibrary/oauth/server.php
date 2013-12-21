<?php
/**
 * Class Server
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class FB
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-jan-06 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroOauthServer extends StileroOauthCommunicator{
    
    private $OauthClient;
    private $OauthUser;
    private $baseString;
    private $signingKey;
    private $signingParams;
    private $authParams;
    private $headers;
    private $authHeader;
    protected $url;
    
    /**
     * Creates a server class for oauth calls
     * @param StileroTTOauthClient $OauthClient
     * @param StileroTTOauthUser $OauthUser
     * @param string $url
     * @param array $postVars
     * @param array $config
     */
    public function __construct(StileroTTOauthClient $OauthClient, StileroTTOauthUser $OauthUser, $url = "", $postVars = "", $config = "") {
        parent::__construct($url, $postVars, $config);
        $this->OauthClient = $OauthClient;
        $this->OauthUser = $OauthUser;
    }

    /**
     * Prepares and sets the parameters for the call
     * @param array $params An array with the params
     */
    private function generateParams(array $params) {
        $oauthDefaults = OauthSignature::oauthDefaults($this->OauthClient->key, $this->OauthUser->accessToken);
        $this->signingParams = array_merge($oauthDefaults, (array)$params);
        if (isset($this->signingParams['oauth_signature'])) {
            unset($this->signingParams['oauth_signature']);
        }
        uksort($this->signingParams, 'strcmp');
        foreach ($this->signingParams as $key => $value) {
            $key = OauthHelper::safeEncode($key);
            $value = OauthHelper::safeEncode($value);
            $_signing_params[$key] = $value;
            $kv[] = "{$key}={$value}";
        }
        $this->authParams = array_intersect_key($oauthDefaults, $_signing_params);
        if (isset($_signing_params['oauth_callback'])) {
            $this->authParams['oauth_callback'] = $_signing_params['oauth_callback'];
            unset($_signing_params['oauth_callback']);
        }
        if (isset($_signing_params['oauth_verifier'])) {
            $this->authParams['oauth_verifier'] = $_signing_params['oauth_verifier'];
            unset($_signing_params['oauth_verifier']);
        }
        $this->signingParams = implode('&', $kv);
    }
    
    /**
     * Sets a HTTP Header for the call
     * @param string $header The HTTP Header
     */
    public function setHeader($header=''){
        $this->headers['Authorization'] = $this->authHeader;
        foreach ($this->headers as $key => $value) {
            $headers[] = trim($key . ': ' . $value);
        }
        $this->header = $headers;
    }
    
    /**
     * Signs the call and sets the header
     * @param string $method Signing Method
     * @param string $url Url for the call
     * @param array $params Params to sign
     * @param boolean $useauth Use auth or not
     */
    private function sign($method, $url, array $params, $useauth=true) {
        $sanitizedURL = OauthHelper::sanitizeURL($url);
        $this->setURL($sanitizedURL);
        $this->generateParams($params);
        if ($useauth) {
            $this->baseString = OauthSignature::baseString($method, $sanitizedURL, $this->signingParams);
            $this->signingKey = OauthSignature::signingKey($this->OauthClient->secret, $this->OauthUser->tokenSecret);
            $this->authParams['oauth_signature'] = OauthSignature::generateSignature($this->baseString, $this->signingKey);
            $this->authHeader = OauthHeader::authorizationHeader($this->authParams);
            $this->setHeader();
        }
    }
    
    /**
     * Sends the Request
     * @param string $url Url for the request
     * @param array $params The params to send
     * @param string $method Post method GET/POST/DELETE/PUT...
     * @param boolean $useauth Set auth
     * @param array $headers array with custom headers
     * @return string The response
     */
    public function sendRequest($url,array $params=array(), $method="POST", $useauth=true, array $headers=array()) {
        if (!empty($headers)){
            $this->headers = array_merge((array)$this->headers, (array)$headers);
        }
        $this->sign($method, $url, $params, $useauth);
        $this->setPostVars($params);
        $response = $this->query();
        return $response;
    }
}
