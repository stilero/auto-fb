<?php
/**
 * Class OAuth Header
 * Class for creating oAuth http headers
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class oAuth
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-aug-02 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 


class StileroOauthHeader {
    const USER_AGENT = 'Stilero OAuth Header Class v1.0';
    const ACCEPT_ALL = '*/*';
    const CONNECTION_CLOSE = 'close';
    const CONNECTION_KEEP_ALIVE = 'Keep-Alive';
    const CONTENT_TYPE_FORM_ENCODED = 'application/x-www-form-urlencoded;charset=UTF-8';
    
    /**
     * Generates an oAuth Header by combining the 7 parameters to a header String
     * @param type $oauthParams
     * @return string Oauth header string
     */
    public static function authorizationHeader(array $oauthParams){
        uksort($oauthParams, 'strcmp');
        foreach ($oauthParams as $key => $value) {
          $keyvalue[] = "{$key}=\"{$value}\"";
        }
        return 'OAuth ' . implode(', ', $keyvalue);
    }
    
    /**
     * Generates and returns an array with general HTTP headers
     * @return array headers
     */
    public static function defaults(){
        $headers[] = "Connection: ".self::CONNECTION_KEEP_ALIVE; 
        $headers[] = "User-Agent: ".self::USER_AGENT; 
        $headers[] = "Content-Type: ".self::CONTENT_TYPE_FORM_ENCODED; 
        return $headers;
    }
}
?>
