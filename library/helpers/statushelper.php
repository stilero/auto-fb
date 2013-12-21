<?php
/**
 * Status Helper class, builds tweets ready for take off.
 *
 * @version  1.1
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-13 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBStatusHelper{
    
    /**
     * Builds and returns a tweet by combining title, hashtags and url
     * @param Object $Article Article Object returned from JArticle Class
     * @param int $numTags Number of tags to use
     * @param string $defaultTag A default tag to use
     * @return string Full Tweet
     */
    public static function buildStatus($Article, $numTags=5, $defaultTag='', $useMetaTags = true){
        $title = $Article->title;
        $metaTags = '';
        if($useMetaTags){
            $metaTags = $Article->tags;
        }
        $hashtagString = StileroAFBTagsHelper::hashTagString($metaTags, $numTags, $defaultTag);
        /*
        $articleSlug = StileroTTArticleHelper::slugFromId($Article->id);
        $categorySlug = StileroTTCategoryHelper::slugFromId($Article->id);
        if(JVERSION){
            $newUrl = ContentHelperRoute::getArticleRoute($Article->id.':'.$Article->alias, $Article->catid);
            // better will be check if SEF option is enable!
            $router = new JRouterSite(array('mode'=>JROUTER_MODE_SEF));
            $newUrl = $router->build($newUrl)->toString(array('path', 'query', 'fragment'));
            // SEF URL !
            $newUrl = str_replace('/administrator/', '', $newUrl);
            //and now the tidying, as Joomlas JRoute makes a cockup of the urls.
            $url = str_replace('component/content/article/', '', $newUrl);
        }else if(StileroTTExtensionHelper::isInstalled('com_sh404sef')){
            $url = StileroTTSH404SEFUrlHelper::sefURL($articleSlug, $categorySlug);
        }else{
            $url = StileroTTUrlHelper::sefURL($articleSlug, $categorySlug);
        }
        */
        $tinyUrl = StileroTTTinyUrlHelper::tinyUrl($Article->url);
        $tweet = $title.' '.$tinyUrl.' '.$hashtagString;
        return $tweet;
    }
}
