<?php
/**
 * Likes Class
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

class StileroFBEndpointLikes extends StileroFBEndpointEndpoint{
    
    protected $postId;
    
    /**
     * Create and read comments on posts (statuses, photos etc)
     * @param \StileroFBOauthAccesstoken $AccessToken
     * @param int $postId ID of the post (Status post id/Photo Id)
     */
    public function __construct(\StileroFBOauthAccesstoken $AccessToken, $postId) {
        parent::__construct($AccessToken);
        $this->postId = $postId;
    }
    
    /**
     * Creates a comment on a post
     * @param string $message The comment message
     * @return string JSON
     */
    public function create(){
        $this->requestUrl = self::$_graph_url.$this->postId.'/likes';
        return $this->sendRequest();
    }
    
    /**
     * Retrieves all comments from a post
     * @return string JSON Response
     */
    public function read(){
        $this->requestUrl = self::$_graph_url.$this->postId.'/likes';
        return $this->sendRequest(self::REQUEST_METHOD_GET);
    }
    
}
