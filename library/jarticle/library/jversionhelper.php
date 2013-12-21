<?php
/**
 * Joomla version helper
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-12 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBJVersionHelper{
    
    const JOOMLA_VERSION_15 = '1.5';
    const JOOMLA_VERSION_16 = '1.6';
    const JOOMLA_VERSION_17 = '1.7';
    const JOOMLA_VERSION_25 = '2.5';
    const JOOMLA_VERSION_30 = '3.0';
    
    /**
     * Returns the version of Joomla installed
     * @return string Version string
     */
    public static function jVersion(){
        if( version_compare(JVERSION,'1.5.0','ge') && version_compare(JVERSION,'1.6.0','lt') ) {
            return self::JOOMLA_VERSION_15;
        }else if( version_compare(JVERSION,'1.6.0','ge') && version_compare(JVERSION,'1.7.0','lt') ) {
            return self::JOOMLA_VERSION_16;
        }else if( version_compare(JVERSION,'1.7.0','ge') && version_compare(JVERSION,'2.5.0','lt') ) {
            return self::JOOMLA_VERSION_17;
        }else if( version_compare(JVERSION,'2.5.0','ge') && version_compare(JVERSION,'3.0.0','lt') ) {
            return self::JOOMLA_VERSION_25;
        }else if( version_compare(JVERSION,'3.0.0','ge') && version_compare(JVERSION,'3.5.0','lt') ) {
            return self::JOOMLA_VERSION_30;
        }
        return '';
    }
    
    /**
     * Checks if the Joomla version installed is 1.5
     * @return boolean true on success
     */
    public static function isJoomla15() {
        if( version_compare(JVERSION,'1.5.0','ge') && version_compare(JVERSION,'1.6.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Checks if the Joomla version installed is 1.6
     * @return boolean true on success
     */
    public static function isJoomla16() {
        if( version_compare(JVERSION,'1.6.0','ge') && version_compare(JVERSION,'1.7.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Checks if the Joomla version installed is 1.7
     * @return boolean true on success
     */
    public static function isJoomla17() {
        if( version_compare(JVERSION,'1.7.0','ge') && version_compare(JVERSION,'2.5.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Checks if the Joomla version installed is 2.5
     * @return boolean true on success
     */
    public static function isJoomla25() {
        if( version_compare(JVERSION,'2.5.0','ge') && version_compare(JVERSION,'3.0.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Checks if the Joomla version installed is 3.0
     * @return boolean true on success
     */
    public static function isJoomla30() {
        if( version_compare(JVERSION,'3.0.0','ge') && version_compare(JVERSION,'3.5.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }
}
