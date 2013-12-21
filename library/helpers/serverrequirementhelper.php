<?php
/**
 * Server Requirement Helper for checking dependancy
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-13 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBServerRequirementHelper{
    
    /**
     * Checks if the server supports curl
     * @return boolean True on success
     */
    public static function hasCurlSupport(){
        if(!function_exists('curl_init')){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * Checks if the server supports file_get_contents functions of PHP
     * @return boolean True on success
     */
    public static function hasFileGetSupport(){
        if(!function_exists('file_get_contents')){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * Checks if server supports json
     * @return boolean true on success
     */
    public static function hasJsonSupport(){
        if(!function_exists('json_decode')){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * Checks if the server is in safe mode
     * @return boolean true on success
     */
    public static function isInSafeMode(){
        if(ini_get('safe_mode')){
            return TRUE;
        }else{
            return false;
        }
    }
    
}
