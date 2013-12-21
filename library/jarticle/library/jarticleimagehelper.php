<?php
/**
 * Image Helper Class for JArticle. 
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

class StileroAFBJArticleImageHelper{
    
    const IMAGE_TYPE_FULL = 'full';
    const IMAGE_TYPE_INTRO = 'intro';
    
    /**
     * Extracts images from the article image object
     * @param string $imageJSON JSON consisting of images
     * @return string image src
     */
    public static function articleImages($imageJSON){
        $obj = json_decode($imageJSON);
        $introImage = ( isset( $obj->{'image_intro'} ) ) ? $obj->{'image_intro'} : '' ;
        $fullImage = ( isset ($obj->{'image_fulltext'}) )? $obj->{'image_fulltext'} : '';
        $images = array(
            'intro' => $introImage,
            'full'  => $fullImage
        );
        return $images;
    }
    
    /**
     * Extracts and returns an image from the text
     * @param Object $Article
     * @param string $type the text type, for example 'full' or 'intro'. Use constants.
     * @return string image src url
     */
    public static function imageFromTextType($Article, $type='full'){
        $images = (isset($Article->images)) ? self::articleImages($Article->images) : '';
        $textImage = (isset($images[$type])) ? $images[$type] : '';
        if($textImage != ""){
            $textImage = preg_match('/http/', $textImage)? $textImage : JURI::root().$textImage;
        }
        return $textImage;
    }
    
    public static function content($Article){
        $content = '';
        if(isset($Article->text)){
            $content = $Article->text;
        }
        if( isset($Article->fulltext) && ($Article->fulltext != '') ){
            $content = $Article->fulltext;
        }
        if( isset($Article->introtext) && ($Article->introtext != '') ){
            $content = $Article->introtext;
        }
        return $content;
    }
    
    /**
     * Extracts images from the content and returns them as an array
     * @param Object $article
     * @return array images
     */
    public static function imagesInContent($Article){
        $content = self::content($Article);
        if( ($content == '') || (!class_exists('DOMDocument')) ){
            return;
        }
        $html = new DOMDocument();
        $html->recover = true;
        $html->strictErrorChecking = false;
        $html->loadHTML($content);
        $images = array();
        foreach($html->getElementsByTagName('img') as $image) {
            $images[] = array(
                'src' => $image->getAttribute('src'),
                'class' => $image->getAttribute('class'),
            );
        }
        return $images;
    }
    
    /**
     * Finds the first image in the content
     * @param Object $article
     * @return string First image in content
     */
    public static function firstImageInContent($Article){
        $content = self::content($Article);
        if( $content == ''){
            return;
        }
        $images = self::imagesInContent($Article);
        $image = (isset($images[0]['src'])) ? $images[0]['src'] : '';
        if($image != ""){
            $image = preg_match('/http/', $image)? $image : JURI::root().$image;
        }
        return $image;
    }
    
    /**
     * Returns an article image for the current article.
     * @param Object $Article
     * @return string image src url
     */
    public static function image($Article){
        $image = self::imageFromTextType($Article, self::IMAGE_TYPE_INTRO);
        if ($image == '' ){
            $image = self::imageFromTextType($Article, self::IMAGE_TYPE_FULL);
        }
        if ($image == '' ){
            $image = self::firstImageInContent($Article);
        }
        return $image;
    }
}

/**
 * Class for extracting images from K2 items
 */
class StileroAFBK2ImageHelper extends StileroAFBJArticleImageHelper{
    
    /**
     * Returns the image url from a K2 item
     * @param stdClass $Article
     * @return string Image url
     */
    public static function introImage($Article){
        $imageUrl = '';
        if(isset($Article->imageMedium)){
            $imageUrl = $Article->imageMedium;
        }
        if($imageUrl=='' && isset($Article->imageLarge)){
            $imageUrl = $Article->imageLarge;
        }
        if($imageUrl=='' && isset($Article->imageXLarge)){
            $imageUrl = $Article->imageXLarge;
        }
        if($imageUrl=='' && isset($Article->imageSmall)){
            $imageUrl = $Article->imageSmall;
        }
        if($imageUrl=='' && isset($Article->imageXSmall)){
            $imageUrl = $Article->imageXSmall;
        }
        $parsedRootUrl = parse_url(JURI::root());
        $parsedImageURL = '';
        if( $imageUrl != '' ){
            $parsedImageURL = str_replace($parsedRootUrl['path'], '', $imageUrl);
        }
        return $parsedImageURL;
    }
    
    /**
     * Extracts all images found from content
     * @param string $textContent Article HTML
     * @return Array array with images from content
     */
    public static function contentImages($textContent){
        if( ($textContent == '') || (!class_exists('DOMDocument')) ){
            return;
        }
        $html = new DOMDocument();
        $html->recover = true;
        $html->strictErrorChecking = false;
        $html->loadHTML($textContent);
        $images = array();
        foreach($html->getElementsByTagName('img') as $image) {
            $images[] = array(
                'src' => $image->getAttribute('src'),
                'class' => $image->getAttribute('class'),
            );
        }
        return $images;
    }
    
    /**
     * Returns first image found in content
     * @param stdClass $Article
     * @return string image url
     */
    public static function fullTextImage($Article){
        $fullText = '';
        $contentImages = array();
        if(isset($Article->fulltext)){
            $fullText = $Article->fulltext;
            $contentImages = self::contentImages($fullText);

        }
        if(empty($contentImages)){
            return;
        }
        $firstContentImage = $contentImages[0]['src'];
        return $firstContentImage;
    }
    
}
