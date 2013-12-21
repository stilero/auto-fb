<?php
/**
 * User class
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

class StileroFBEndpointUser extends StileroFBEndpointEndpoint{
    
    protected $userId;
    
    public function __construct(\StileroFBOauthAccesstoken $AccessToken, $userId = 'me') {
        parent::__construct($AccessToken);
        $this->userId = $userId;
    }
    
    /**
     * Retrieves info about the logged in person
     * @return string JSON string
     */
    public function me(){
        $this->requestUrl = self::$_graph_url.'me';
        return $this->sendRequest(self::REQUEST_METHOD_GET);
    }
    
    /**
     * Retrieves a list of the accounts the user administrates
     * Permissions
     *      A user access token with manage_pages is required, and will only allow the retrieval for that specific person.
     * @return string JSON Response
     */
    public function accounts(){
        $this->requestUrl = self::$_graph_url.$this->userId.'/accounts';
        return $this->sendRequest(self::REQUEST_METHOD_GET);
    }
    
    /**
     * Retrieves a list of the accounts the user administrates
     * Permissions
     *      A user access token with user_groups permission is required to view the groups that person is a member of.
     *      A user access token with friend_groups permission is required to view the groups that person's friends are members of.
     * @return string JSON Response
     */
    public function groups(){
        $this->requestUrl = self::$_graph_url.$this->userId.'/groups';
        return $this->sendRequest(self::REQUEST_METHOD_GET);
    }
    
    /**
     * Retrieves the Profile picture of a user
     * @param bool $redirect if true, the image itself is retrieved, otherwise a JSON response is retrieved
     * @param string $type You use this to get a pre-specified size of picture. enum{square,small,normal,large}
     * @param int $height Restrict the picture height to this size in pixels.
     * @param int $width Restrict the picture width to this size in pixels. 
     * When height and width are both used, the image will be scaled as close to 
     * the dimensions as possible and then cropped down.
     * @return string JSON Response
     */
    public function picture($redirect='false', $type='', $height='', $width=''){
        $this->requestUrl = self::$_graph_url.$this->userId.'/picture';
        if($redirect != ''){
            $this->params['redirect'] = $redirect;
        }
        if($type != ''){
            $this->params['type'] = $type;
        }
        if($height != ''){
            $this->params['height'] = $height;
        }
        if($width != ''){
            $this->params['width'] = $width;
        }
        return $this->sendRequest(self::REQUEST_METHOD_GET);
    }
    
    /**
     * Returns the permissions that this person has granted to the app making the request.
     * @return string JSON
     */
    public function permissions(){
        $this->requestUrl = self::$_graph_url.$this->userId.'/permissions';
        return $this->sendRequest(self::REQUEST_METHOD_GET);
    }
    
    /**
     * Returns a token for the page requested
     * @param string $id the page id
     * @return string access token for the page
     */
    public function getTokenForPageWithId($id){
        $accountList = $this->accounts();
        $accounts = StileroFBOauthResponse::handle($accountList);
        if(isset($accounts->data)){
            foreach ($accounts->data as $account) {
                if($account->id == $id){
                    return $account->access_token;
                }
            }
        }
        return null;
    }
}
