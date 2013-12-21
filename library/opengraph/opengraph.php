<?php
/**
 * Open graph Class
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_autofbook
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-21 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroFBOpengraph{
    protected $isTagsAdded;
    protected $article;
    
    /**
     * Class for inserting OG meta tags in HTML code
     * @param tableobject $article Article object from the Joomla contentSave method
     */
    public function __construct($article) {
        $JArticle = new StileroAFBJarticle($article);
        $this->article = $JArticle->getArticleObj();
        $this->isTagsAdded = FALSE;
    }
    
    /**
     * Builds and returns an array with metatags
     * @return array MetaTags
     */
    protected function metatags(){
        $config = JFactory::getConfig();
        $title = htmlentities(strip_tags( $this->article->title), ENT_QUOTES, "UTF-8");
        $image = $this->article->image;
        $description = $this->article->description;
        $siteName = $config->get( 'config.sitename' );
        $language = JFactory::getLanguage();
        $locale = str_replace( "-", "_", $language->getTag() );
        $published = date('c', strtotime( $this->article->publish_up) );
        $category = htmlentities(strip_tags($this->article->category_title), ENT_QUOTES, "UTF-8");
        $modified = date('c', strtotime( $this->article->modified) );
        $url = $this->article->url;
        $metatags = array(
            'og:title'          =>  $title,
            'og:image'          =>  $image,
            'og:description'    =>  $description,
            'og:locale'         =>  $locale,
            'og:type'           =>  'article',
            'article:published_time'    =>  $published,
            'article:section'           =>  $category,
            'article:modified_time'     =>  $modified,
            'og:site_name'      =>  $siteName,
            'og:url'            =>  $url
        );
        return $metatags;
    }
    
    /**
     * Checks if the current document is a HTML document
     * @return boolean
     */
    protected function isHTMLDocument(){
        $document = JFactory::getDocument();
        $doctype    = $document->getType();
        if ( $doctype !== 'html' ) { 
            return false; 
        }else{
            return true;
        }
    }
    
    /**
     * Adds the meta tags to the HTML document
     */
    public function addTags(){
        if($this->isHTMLDocument() && !$this->isTagsAdded){
            $metatags = $this->metatags();
            $document = JFactory::getDocument();
            foreach ($metatags as $key => $value) {
                if($value !="") {
                    $meta = '<meta property="'.$key.'" content="'.$value.'" />';
                    $document->addCustomTag($meta);
                }
            }
            $this->isTagsAdded = TRUE;
        }
    }
    
    
}
