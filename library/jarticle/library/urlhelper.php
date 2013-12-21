<?php
/**
 * StileroTTURL Class
 * 
 * URL helper class
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

class StileroAFBUrlHelper{
    
    /**
     * Returns a SEF URL from the article and Category Slug
     * @param string $articleSlug
     * @param string $categorySlug
     * @return string SefURL - Search Engine Friendly URL
     */
    public static function sefURL($articleSlug, $categorySlug){
        if(StileroAFBJVersionHelper::isJoomla30()){
            $sefUrl = JRoute::_('index.php?view=article&id='.$articleSlug, FALSE);
            $sefUrlNoAdmin = str_replace('/administrator', '', $sefUrl);
            $parsedUrl = parse_url(JUri::root());
            $fullUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$sefUrlNoAdmin;
            return $fullUrl;
        }
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $siteURL = substr(JURI::root(), 0, -1);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        $articleRoute = JRoute::_( ContentHelperRoute::getArticleRoute($articleSlug, $categorySlug) );
        $sefURI = str_replace(JURI::base(true), '', $articleRoute);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            $siteURL = str_replace($siteURL.DS.'administrator', '', $siteURL);
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $sefURL = $siteURL.$sefURI;
        return $sefURL;
    }    
}

class StileroAFBK2UrlHelper{
    
    /**
     * Returns the url of the article
     * @param stdClass $Article
     * @param string $categorySlug
     * @return string full url
     */
    public static function sefURL($Article){
        $articleID = '';
        $catSlug = '';
        if(isset($Article->id) && (isset($Article->alias))){
            $articleID = '&id='.$Article->id.':'.$Article->alias;
        }
        if(isset($Article->catslug)){
            $catSlug = '&catid='.$Article->catslug;
        }
        $routedUrl = JRoute::_( 'index.php?option=com_k2&view=item' . $catSlug . $articleID);
        $sanitizedUrl = str_replace('/administrator', '', $routedUrl);
        $parsedRootURL = parse_url(JURI::root());
        $host = str_replace("/administrator", "", $parsedRootURL['host']);
        $fullUrl = preg_match('/http/', $sanitizedUrl)? $sanitizedUrl :  $parsedRootURL['scheme'].'://'.$host. $sanitizedUrl;
        return $fullUrl;
    }
}
