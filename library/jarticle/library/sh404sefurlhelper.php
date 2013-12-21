<?php
/**
 * Helper Class that uses SH404SEF to produce SEF Urls.
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

class StileroAFBSH404SEFUrlHelper{
    
    /**
     * Initializes the SH404SEF component and sets the router to use SH404SEF.
     */
    protected static function _initializeSH404SEF(){
        $app = &JFactory::getApplication();
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'sh404sef.class.php');
        $sefConfig = & Sh404sefFactory::getConfig();

        // hook to be able to install other SEF extension plugins
        Sh404sefHelperExtplugins::loadInstallAdapters();

        // another hook to allow other SEF extensions language file to be loaded
        Sh404sefHelperExtplugins::loadLanguageFiles();

        if (!$sefConfig->Enabled) {
            // go away if not enabled
            return;
        }
        $joomlaRouter = $app->getRouter();
        $pageInfo = & Sh404sefFactory::getPageInfo();
        $pageInfo->router = new Sh404sefClassRouter();
        $joomlaRouter->attachParseRule( array( $pageInfo->router, 'parseRule'));
        $joomlaRouter->attachBuildRule( array( $pageInfo->router, 'buildRule'));
    }

    /**
     * Returns a SEF URL with the help of SH404SEF
     * @param string $articleSlug Article Slug
     * @param string $categorySlug Category Slug
     * @return string SEFUrl
     */
    public static function sefURL($articleSlug, $categorySlug){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $siteURL = substr(JURI::root(), 0, -1);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        if(JPATH_BASE == JPATH_ADMINISTRATOR){
            self::_initializeSH404SEF();
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
