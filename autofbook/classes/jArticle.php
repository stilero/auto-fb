<?php

/**
 * A factory class for making standard object from Joomla articles.
 * 
 * $Id: jArticle.php 14 2012-05-03 09:13:02Z webbochsant@gmail.com $
 * @author Daniel Eliasson <joomla at stilero.com>
 * @license	GPLv3
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of jArticle.
 * 
 * jArticle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * jArticle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with jArticle.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */
class jArticle {
    var $articleObj;
    
    public function __construct($article) {
        $tempClass = new stdClass();
        foreach ($article as $property => $value) {
            $tempClass->$property = $value;
            //unset($object->$property);
        }
        //unset($value);
        //$object = (unset)$object;
        $tempClass->jVersion = $this->jVersion();
        $tempClass->category_title = ($tempClass->jVersion == '1.5') ? $tempClass->category : $tempClass->category_title;
        $tempClass->description = $this->description($article);
        $tempClass->isPublished = $this->isPublished($article);
        $tempClass->isPublic = $this->isPublic($article);
        $tempClass->image = $this->image($article);
        $tempClass->firstContentImage = $this->firstImageInContent($article);
        $tempClass->introImage = $this->introImage($article);
        $tempClass->fullTextImage = $this->fullTextImage($article);
        $tempClass->imageArray = $this->imagesInContent($article);
        $tempClass->url = $this->url($article);
        $tempClass->tags = $this->tags($article);
        $this->articleObj = $tempClass;
    }
    
    public function getArticleObj(){
        return $this->articleObj;
    }
    
    public function categoryTitle($article){
        if ($this->jVersion() == '1.5'){
            $category_title = isset($article->category) ? $article->category : '';
        }else{
            $category_title = isset($article->category_title) ? $article->category_title : '';
        }
        return $category_title;
    }


    public function image($article){
        $image = $this->introImage($article);
        if ($image == '' ){
            $image = $this->fullTextImage($article);
        }
        if ($image == '' ){
            $image = $this->firstImageInContent($article);
        }
        return $image;
    }
    
    public function imagesInContent($article){
        $content = $article->text;
        //var_dump($content);exit;
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
    
    public function firstImageInContent($article){
        if(!isset($article->text)) return;
        $images = $this->imagesInContent($article);
        $image = (isset($images[0]['src'])) ? $images[0]['src'] : '';
        if($image != ""){
            $image = preg_match('/http/', $image)? $image : JURI::root().$image;
        }
        return $image;
    }
    
    protected function introImage($article){
        $images = (isset($article->images)) ? $this->articleImages($article->images) : '';
        $introImage = (isset($images['intro'])) ? $images['intro'] : '';
        if($introImage != ""){
            $introImage = preg_match('/http/', $introImage)? $introImage : JURI::root().$introImage;
        }
        return $introImage;
    }
    
    protected function fullTextImage($article){
        $images = (isset($article->images)) ? $this->articleImages($article->images) : '';
        $fullTextImage = (isset($images['full'])) ? $images['full'] : '';
        if($fullTextImage != ""){
            $fullTextImage = preg_match('/http/', $fullTextImage)? $fullTextImage : JURI::root().$fullTextImage;
        }
        return $fullTextImage;
    }
    
    protected function articleImages($imageJSON){
        $obj = json_decode($imageJSON);
        $introImage = ( isset( $obj->{'image_intro'} ) ) ? $obj->{'image_intro'} : '' ;
        $fullImage = ( isset ($obj->{'image_fulltext'}) )? $obj->{'image_fulltext'} : '';
        $images = array(
            'intro' => $introImage,
            'full'  => $fullImage
        );
        return $images;
    }
    
    public function description($article){
        $description = isset($article->text) ? substr(htmlentities(strip_tags($article->text)), 0, 150) : '';
        if(isset($article->introtext)){
            $description = htmlentities(strip_tags($article->introtext));
        }elseif (isset($article->metadesc)) {
            $description = htmlentities(strip_tags($article->metadesc));
        }
        return $description;
    }
    
    private function tags($article) {
        $metatagString = isset($article->metakey) ? $article->metakey : '';
        if($metatagString == ""){
            return;
        }
       $tags = explode(",", $metatagString);
       foreach ($tags as $key => $value) {
           $tagsArray[] = trim(str_replace(" ", "", $value));
       }
       return $tagsArray;
    }
    
    public function url($article){
        $catid = isset($article->catslug) ? '&catid='.$article->catslug : '';
        $articleID = isset($article->slug) ? '&id=' . $article->slug : '';
        $url = JRoute::_( 'index.php?view=article' . $catid . $articleID);
        $parsedRootURL = parse_url(JURI::root());
        $fullUrl = preg_match('/http/', $url)? $url :  $parsedRootURL['scheme'].'://'.$parsedRootURL['host']. $url;
        return $fullUrl;
    }
    
    public function isPublic($article){
        if(!isset($article->access)){
            return FALSE;
        }
        $access = $article->access;
        if($this->jVersion() == '1.5'){
            $isPublic = $access=='0' ? TRUE : FALSE;
        }else{
            $isPublic = $access=='1' ? TRUE : FALSE;
        }
        return $isPublic;
    }
    
    public function jVersion(){
        if( version_compare(JVERSION,'1.5.0','ge') && version_compare(JVERSION,'1.6.0','lt') ) {
            return '1.5';
        }else if( version_compare(JVERSION,'1.6.0','ge') && version_compare(JVERSION,'1.7.0','lt') ) {
            return '1.6';
        }else if( version_compare(JVERSION,'1.7.0','ge') && version_compare(JVERSION,'2.5.0','lt') ) {
            return '1.7';
        }else if( version_compare(JVERSION,'2.5.0','ge') && version_compare(JVERSION,'3.0.0','lt') ) {
            return '2.5';
        }
        return '';
    }
    
    public function isPublished($article){
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return false;
        }
        $date = JFactory::getDate();
        $currentDate = $date->toMySQL();
        if ( ($publishUp > $currentDate) ){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00'){
            return FALSE;
        }else {
            return TRUE;
        }
    }
}

/**
 * For K2 items
 */
class k2Article extends jArticle{
    public function __construct($article) {
        parent::__construct($article);
        $tempClass->category_title = $this->categoryTitle($article);
    }
    
    public function categoryTitle($article){
        $category_title = $article->category->name;
        return $category_title;
    }
    
    public function url($article){
        $catid = isset($article->catslug) ? '&catid='.$article->catslug : '';
        $articleID = isset($article->id) ? '&id=' . $article->id . ':'.  $article->alias : '';
        $url = JRoute::_( 'index.php?option=com_k2&view=item' . $catid . $articleID);
        $parsedRootURL = parse_url(JURI::root());
        $fullUrl = preg_match('/http/', $url)? $url :  $parsedRootURL['scheme'].'://'.$parsedRootURL['host']. $url;
        return $fullUrl;
    }
   
}

/**
 * For Zoo articles
 */
class zooArticle extends jArticle{
    public function __construct($article) {
        parent::__construct($article);
    }
}

/**
 * For VirtueMart
 */
class vmArticle extends jArticle{
    public function __construct($article) {
        parent::__construct($article);
    }
}
?>
