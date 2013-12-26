<?php
/**
 * Convenient class for contexts
 *
 * @version  1.1
 * @package Stilero
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-sep-15 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBContextHelper{
    
    const CONTENT_FEATURED = 'com_content.featured'; //Featured articles in frontend
    const CONTENT_ARTICLE = 'com_content.article'; //Regular article in frontend
    const MEDIA_FILE = 'com_media.file'; //Picking image in the media manager
    const K2_ITEM = 'com_k2.item'; //K2 item in frontend
    
    /**
     * Returns an array with all contexts related to articles
     * @return Array Array with all article contexts defined
     */
    public static function articleContexts(){
        $articleContexts = array(
            self::CONTENT_FEATURED,
            self::CONTENT_ARTICLE,
            self::K2_ITEM
        );
        return $articleContexts;
    }
    
    /**
     * Checks if the current context is an article
     * @param string $context
     * @return boolean True if current context is article
     */
    public static function isArticle($context){
        if(in_array($context, self::articleContexts())){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Checks if the current context is an image
     * @param string $context
     * @return boolean True if current context is image
     */
    public static function isImage($context){
        if($context == self::MEDIA_FILE){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}
