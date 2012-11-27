<?php

/**
 * A factory class for making standard object from Joomla articles.
 * 
 * $Id: jArticle.php 24 2012-09-18 19:27:16Z webbochsant@gmail.com $
 * @version $Rev: 24 $
 * @author Daniel Eliasson <joomla at stilero.com>
 * @license	GPLv3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

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
        //$tempClass->category_title = ($tempClass->jVersion == '1.5') ? $tempClass->category : $tempClass->category_title;
        $tempClass->category_title = $this->categoryTitle($article);
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
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' > introImage='.$image );
        if ($image == '' ){
            $image = $this->fullTextImage($article);
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' > fullTextImage='.$image );
        }
        if ($image == '' ){
            $image = $this->firstImageInContent($article);
            if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' > firstImgInCont='.$image );
        }
        return $image;
    }
    
    public function imagesInContent($article){
        $content = $article->text;
        $content = $content == '' ? $article->fulltext : $content;
        $content = $content == '' ? $article->introtext : $content;
        //if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' > content='.$content );
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
        //if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__.' > imagesInContent='.implode(', ', $images[0]) );
        return $images;
    }
    
    public function firstImageInContent($article){
        $content = $article->text;
        $content = $content == '' ? $article->fulltext : $content;
        $content = $content == '' ? $article->introtext : $content;
        if( $content == ''){
            return;
        }
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
        $descText = $article->text!="" ? $article->text : '';
        $description = $article->text!="" ? $article->text : '';
        if(isset($article->introtext) && $article->introtext!=""){
            $descText = $article->introtext;
        }elseif (isset($article->metadesc) && $article->metadesc!="" ) {
            $descText = $article->metadesc;
        }
        $descNeedles = array("\n", "\r", "\"", "'");
        str_replace($descNeedles, " ", $description );
        $description = substr(htmlspecialchars( strip_tags($descText), ENT_COMPAT, 'UTF-8'), 0, 250);
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
    
    private function _joomlaSefUrlFromRoute($article){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $siteURL = substr(JURI::root(), 0, -1);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        $catAlias = $this->categoryAlias($article);
        $articleSlug = $this->articleSlug($article);
        $catSlug = $article->catid.':'.$catAlias;
        $isSh404SefExtensionEnabled = FALSE;
        if($this->isExtensionInstalled('com_sh404sef')){
             $isSh404SefExtensionEnabled = JComponentHelper::isEnabled('com_sh404sef', true);
        }
        if($isSh404SefExtensionEnabled && JPATH_BASE == JPATH_ADMINISTRATOR){
            $this->_initSh404SefUrls();
        }
        $articleRoute = JRoute::_( ContentHelperRoute::getArticleRoute($articleSlug, $catSlug) );
        $sefURI = str_replace(JURI::base(true), '', $articleRoute);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            $siteURL = str_replace($siteURL.DS.'administrator', '', $siteURL);
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $sefURL = $siteURL.$sefURI;
        return $sefURL;
    }
    private function _initSh404SefUrls(){
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
    
    protected function isExtensionInstalled($option){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id AS id, element AS "option", params, enabled');
        $query->from('#__extensions');
        $query->where($query->qn('type') . ' = ' . $db->quote('component'));
        $query->where($query->qn('element') . ' = ' . $db->quote($option));
        $db->setQuery($query);
        //$result = $db->query($query);
        $result = $db->loadObject();
        if($result == null){
            return false;
        }
        return TRUE;
    }
    
    public function url($article){
        return $this->_joomlaSefUrlFromRoute($article);
    }
    
    private function articleSlug($article){
        $slug = $article->id.':'.$this->articleAlias($article);
        return $slug;
    }
    
    private function articleAlias($article){
        jimport( 'joomla.filter.output' );
        $alias = $article->alias;
        if(empty($alias)) {
            $db =& JFactory::getDBO();
            $query = 
                'SELECT a.alias FROM '
                .$db->nameQuote('#__content').' AS '.$db->nameQuote('a').
                ' WHERE a.id='.$db->quote($article->id);
            $db->setQuery($query);
            $result = $db->loadObject();
            $alias = empty($result->alias) ? $article->title : $result->alias;
        }
        $alias = JFilterOutput::stringURLSafe($alias);
        return $alias;
    }
    
    private function categoryAlias($article){
        jimport( 'joomla.filter.output' );
        $db =& JFactory::getDBO();
        $query = 
            'SELECT c.alias FROM '
            .$db->nameQuote('#__categories').' AS '.$db->nameQuote('c').
            ' WHERE c.id='.$db->quote($article->catid);
        $db->setQuery($query);
        $result = $db->loadObject();
        $alias = $result->alias;
        $alias = JFilterOutput::stringURLSafe($alias);
        return $alias;
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
        if(JDEBUG) JFactory::getApplication()->enqueueMessage( var_dump($article));
        $isPublState = $article->state == '1' ? true : false;
        if(!$isPublState){
            return FALSE;
        }
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return false;
        }
        $date = JFactory::getDate();
        $currentDate = $date->toSql();
        if ( ($publishUp > $currentDate) ){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
            return FALSE;
        }else {
            return TRUE;
        }
    }
    
    public function isArticle(){
        $hasID = isset($this->articleObj->id) ? TRUE : FALSE;
        $hasTitle = isset($this->articleObj->title) ? TRUE : FALSE;
        if($hasID && $hasTitle){
            return TRUE;
        }
        return FALSE;
    }

    public function __get($name) {
        return $this->$name;
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
    
    protected function introImage($article){
        $imageUrl = '';
        if(isset($article->imageMedium)){
            $imageUrl = $article->imageMedium;
        }
        if($imageUrl=='' && isset($article->imageLarge)){
            $imageUrl = $article->imageLarge;
        }
        if($imageUrl=='' && isset($article->imageXLarge)){
            $imageUrl = $article->imageXLarge;
        }
        if($imageUrl=='' && isset($article->imageSmall)){
            $imageUrl = $article->imageSmall;
        }
        if($imageUrl=='' && isset($article->imageXSmall)){
            $imageUrl = $article->imageXSmall;
        }
        $parsedRootUrl = parse_url(JURI::root());
        $parsedImageURL = '';
        if( $imageUrl != '' ){
            $parsedImageURL = str_replace($parsedRootUrl['path'], '', $imageUrl);
        }
        return $parsedImageURL;
    }
    
    protected function fullTextImage($article){
        $fullText = '';
        $contentImages = array();
        if(isset($article->fulltext)){
            $fullText = $article->fulltext;
            $contentImages = $this->imagesInTextContent($fullText);

        }
        if(empty($contentImages)){
            return;
        }
        $firstContentImage = $contentImages[0]['src'];
        return $firstContentImage;
    }
    
    public function imagesInTextContent($textContent){
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
    
    public function isPublished($article){
        if(JDEBUG) JFactory::getApplication()->enqueueMessage( var_dump($article));
        $isPublished = false;
        if(isset($article->published)){
            $isPublished = $article->published;
        }
        if($isPublished == FALSE){
            return FALSE;
        }
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        $date = JFactory::getDate();
        $currentDate = $date->toSql();
        if($publishUp > $currentDate){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
            return FALSE;
        }else {
            return TRUE;
        }
        return TRUE;
    }
    
    public function categoryTitle($article){
        $category_title = '';
        if(isset($article->category->name)){
            $category_title = $article->category->name;
        }
        return $category_title;
    }
    
    public function url($article){
        $catid = isset($article->catslug) ? '&catid='.$article->catslug : '';
        $articleID = isset($article->id) ? '&id=' . $article->id . ':'.  $article->alias : '';
        $url = JRoute::_( 'index.php?option=com_k2&view=item' . $catid . $articleID);
        $url = str_replace('/administrator', '', $url);
        $parsedRootURL = parse_url(JURI::root());
        $host = str_replace("/administrator", "", $parsedRootURL['host']);
        $fullUrl = preg_match('/http/', $url)? $url :  $parsedRootURL['scheme'].'://'.$host. $url;
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
    
    var $productImage;
    
    public function __construct($article) {
        parent::__construct($article);
        $this->articleObj->modified = $article->modified_on;
        $this->articleObj->created = $article->created_on;
        $this->articleObj->title = $article->product_name;
        $this->articleObj->catid = $article->virtuemart_category_id;
        if($article->virtuemart_product_id != ""){
            $this->articleObj->id = $article->virtuemart_product_id;
        }else if($article->virtuemart_product_id != ""){
            $this->articleObj->id = $article->product_id;
        }

    }
    
    public function categoryTitle($article){
        $category_title = isset($article->category_name) ? $article->category_name : '';
        return $category_title;
    }
    
    public function description($article){
        $descText = $article->product_desc != "" ? $article->product_desc : '';
        //$description = $article->text!="" ? $article->text : '';
        if(isset($article->product_s_desc) && $article->product_s_desc != ""){
            $descText = $article->product_s_desc;
        }
        $descNeedles = array("\n", "\r", "\"", "'");
        $descText = str_replace($descNeedles, " ", $descText );
        $description = substr(htmlspecialchars( strip_tags($descText), ENT_COMPAT, 'UTF-8'), 0, 250);
        return $description;
    }
    
    public function isPublished($article){
        if($article->published == '1' ){
            return true;
        }
        return false;
    }
    
    public function isPublic($article){
        return true;
    }
    
    public function image($article){
        if($this->productImage != ''){
            return $this->productImage;
        }
        $db =& JFactory::getDBO();
        $query =
            'SELECT medias.file_url_thumb'.
            ' FROM '.$db->nameQuote('#__virtuemart_product_medias').' AS '.$db->nameQuote('xref').
            ' LEFT JOIN '.$db->nameQuote('#__virtuemart_medias').' AS '.$db->nameQuote('medias').
            ' ON medias.virtuemart_media_id = xref.virtuemart_media_id'.
            ' WHERE xref.virtuemart_product_id = '.$db->quote($article->virtuemart_product_id)
        ;
        $db->setQuery($query);    
        $imagePath = $db->loadResult();
        $this->productImage = $imagePath!='' ? JURI::root().$imagePath : '';
        return $this->productImage;
    }
    
    public function firstImageInContent($article){
        return $this->image($article);
    }
    
    public function introImage($article){
        return $this->image($article);
    }
    public function fullTextImage($article){
        return $this->image($article);
    }
    public function imagesInContent($article){
        $images = array($this->image($article));
        return $images;
    }
    
    public function url($article){
        $u =& JURI::getInstance( JURI::root() );
        $host = $u->getHost();
        $scheme = $u->getScheme();
        $url = $scheme.'://'.$host.$article->link;
        return $url;
    }
}