<?php
/**
 * Class for FB Apps
 *
 * @version  1.0
 * @package Stilero
 * @subpackage class-oauth-fb
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-19 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroFBOauthApp extends StileroOauthClient{
    
    public $id;
    public $secret;
    
    /**
     * Generates App Object
     * @param string $id App ID
     * @param string $secret App Secret
     */
    public function __construct($id, $secret) {
        parent::__construct($id, $secret);
        $this->id = $id;
    }
    
    public function __get($name) {
        if($name == 'id'){
            return $this->key;
        }
    }

}
