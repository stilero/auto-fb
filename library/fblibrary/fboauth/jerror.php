<?php
/**
 * class-oauth-fb
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

class JError{
    
    /**
     * Prints out a JSON error and halts
     * @param int $code
     * @param string $message
     */
    public static function raiseError($code, $message){
       $response = <<<EOD
{
   "error": {
      "code": "$code",
      "type": "OAuthException",
      "message": "$message"
   }
}
EOD;
       print $response;
    }
}
