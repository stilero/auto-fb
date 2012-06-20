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
        $description = $article->text!="" ? $article->text : '';
        if(isset($article->introtext) && $article->introtext!=""){
            $description = $article->introtext;
        }elseif (isset($article->metadesc) && $article->metadesc!="" ) {
            $description = $article->metadesc;
        }
        $descNeedles = array("\n", "\r", "\"", "'");
        str_replace($descNeedles, " ", $description );
        $description = substr(htmlentities(strip_tags($description)), 0, 250);
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
    
    private function joomlaSefUrl($article){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $siteURL = substr(JURI::root(), 0, -1);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        $articleRoute = JRoute::_( ContentHelperRoute::getArticleRoute($article->id, $article->catid) );
        $sefURI = str_replace(JURI::base(true), '', $articleRoute);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            $siteURL = str_replace($siteURL.DS.'administrator', '', $siteURL);
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $sefURL = $siteURL.$sefURI;
        //var_dump($sefURI);;exit;
        return $sefURL;
    }
    
    private function joomlaSefUrlFromRoute($article){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $siteURL = substr(JURI::root(), 0, -1);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        //$articleAlias = $this->articleAlias($article);
        $catAlias = $this->categoryAlias($article);
        $articleSlug = $this->articleSlug($article);
        $catSlug = $article->catid.':'.$catAlias;
        $isSh404SefExtensionEnabled = JComponentHelper::isEnabled('com_sh404sef', true);
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
    
    public function seftestURL($article){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        $live_site = substr(JURI::root(), 0, -1);
        
        $articleAlias = $this->articleAlias($article);
        $catAlias = $this->categoryAlias($article);
        //$catSlug = empty($article->catslug) ?  '&catid='.$article->catid.':'.$catAlias : '&catid='.$article->catslug;
        $articleSlug = empty($article->slug) ?  '&id='.$article->id.':'.$articleAlias : '&id=' . $article->slug;
        //$baseUrl = str_replace('administrator'.DS, '', JURI::root(true).DS); 
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            // In the back end we need to set the application to the site app instead
            JFactory::$application = JApplication::getInstance('site');
        }
        $urls = JRoute::_( 'index.php?view=article' . $catSlug . $articleSlug);
        
        //$urls =  JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
        $urls = str_replace(JURI::base(true), '', $urls);
        if(JPATH_BASE == JPATH_ADMINISTRATOR) {
            $live_site = str_replace($live_site.DS.'administrator', '', $live_site);
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $urls = $live_site.$urls;
        JError::raiseNotice('0','url='.$urls);
        return $urls;
    }
    
    public function testSEFURL($article){
        $urls = JRoute::_( 'index.php?option=com_content&view=article&id='.$this->articleSlug($article).'&catid='.$article->catid);
        return $urls;
    }
    
    public function testBSefURL($article){
        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        require_once(JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'application'.DS.'router.php');
        require_once(JPATH_SITE.DS.'includes'.DS.'router.php');
        $newUrl = ContentHelperRoute::getArticleRoute($article->id.':'.$this->articleAlias($article), $article->catid);
        // better will be check if SEF option is enable!
        $router = new JRouterSite(array('mode'=>JROUTER_MODE_SEF));
        $newUrl = $router->build($newUrl)->toString(array('path', 'query', 'fragment'));
        // SEF URL !
        $newUrl = str_replace('/administrator/', '', $newUrl);
        //and now the tidying, as Joomlas JRoute makes a cockup of the urls.
        $newUrl = str_replace('component/content/article/', '', $newUrl);
        return $newUrl;
    }
    
    private function testCSefURL($article){
        require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'sh404sef.class.php');
        $joomlaRouter = $app->getRouter();
        $pageInfo = & Sh404sefFactory::getPageInfo();
        $pageInfo->router = new Sh404sefClassRouter();
    }


    public function url($article){
        //return $this->testCSefURL($article);
        return $this->joomlaSefUrlFromRoute($article);
    //jimport( 'joomla.application.router' );
    //require_once (JPATH_ROOT . DS . 'includes' . DS . 'router.php');
    //require_once (JPATH_ROOT . DS . 'includes' . DS . 'application.php');
        
    
        $articleAlias = $this->articleAlias($article);
        $catAlias = $this->categoryAlias($article);
        $catSlug = empty($article->catslug) ?  '&catid='.$article->catid.':'.$catAlias : '&catid='.$article->catslug;
        $articleSlug = empty($article->slug) ?  '&id='.$article->id.':'.$articleAlias : '&id=' . $article->slug;
        JError::raiseNotice('0','aSlug='.$articleSlug);
        JError::raiseNotice('0','cSlug='.$catSlug);
        $baseUrl = str_replace('administrator'.DS, '', JURI::root(true).DS); 
        $url = JRoute::_( 'index.php?view=article' . $catSlug . $articleSlug);




        //$relUrl = JRoute::_( 'index.php?view=article' . $catSlug . $articleSlug);
        //JError::raiseNotice('0','url='.$url);
        //JError::raiseNotice('0','rel='.$urls);
        $parsedRootURL = parse_url($baseUrl);
        $fullUrl = preg_match('/http/', $url)? $url :  $parsedRootURL['scheme'].'://'.$parsedRootURL['host']. $url;
        JError::raiseNotice('0','url='.$fullUrl);
        return $fullUrl;
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
        $publishUp = isset($article->publish_up) ? $article->publish_up : '';
        $publishDown = isset($article->publish_down) ? $article->publish_down : '';
        if($publishUp == '' ){
            return false;
        }
        $date = JFactory::getDate();
        $currentDate = $date->toMySQL();
        if ( ($publishUp > $currentDate) ){
            return FALSE;
        }else if($publishDown < $currentDate && $publishDown != '0000-00-00 00:00:00' && $publishDown!=""){
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
