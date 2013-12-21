<?php
/**
 * Class for Access Codes
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

class StileroFBOauthCode{
    
    public $code;
    
    /**
     * Cleans strings and strips out unwanted characters
     * @param string $string
     * @return string cleaned string
     */
    public static function clean($string){
        $cleaned = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $string);
        $cleaned = ltrim($cleaned, '.');
        return $cleaned;
    }
    
    /**
     * Fetches the oauth code from a get request
     */
    public function fetchCode(){
        if(isset($_GET['code'])){
            $cleanedCode = self::clean($_GET['code']);
            $this->code = $cleanedCode;
        }
    }
    
    /**
     * Checks if a code variable is found in the received GET-request
     * @return boolean true if found
     */
    public static function hasCodeInGetRequest(){
        if(isset($_GET['code'])){
            return true;
        }else{
            return false;
        }
    }
}
