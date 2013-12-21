<?php
/**
 * Class for making necessary checks. Dependent on stileroTT helpers.
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

class StileroAFBSharecheck{
    
    protected $_JArticle;
    protected $_article;
    protected $_Table;
    protected $_delay;
    protected $_dateLimit;
    protected $_categories;
    protected $_isOverridingDelayCheck;
    protected $_isBackend;
    
    /**
     * Class for checking before posts
     * @param stdClass $Article Article object/item straight from Joomla
     * @param StileroTTShareTable $LogTable Log Table object
     * @param int $delay Minutes between posts
     * @param date $dateLimit A date to only post newer than this date, for example 2013-08-13.
     * @param string $catList Comma separated list of categories to post, for example 2,3,5,7
     * @param boolean $isOverridingDelayCheck Set this to allways post on save
     * @param string $option the joomla option (com_content/com_k2) use the constants of the JArticle class
     * @param boolean $isBackend True if called from backend
     */
    public function __construct($article, StileroAFBTable $LogTable, $delay=5, $dateLimit='', array $categories=array(), $isOverridingDelayCheck=false, $isBackend=true, $option='com_content') {
        $this->_article = $article;
        $this->_Table = $LogTable;
        $this->_delay = $delay;
        $this->_dateLimit = $dateLimit;
        $this->_categories = $categories;
        $this->_isOverridingDelayCheck = $isOverridingDelayCheck;
        $this->_isBackend = $isBackend;
        $this->JArticleObject($article, $option);
    }
    
    /**
     * Instantiates the JArticle class and sets the object to the JArticle object
     * @param stdClass $article Joomla article class
     * @param string $option the joomla option (com_content/com_k2) use the constants of the JArticle class
     */
    protected function JArticleObject($article, $option){
        $JArticle = null;
        if($option == 'com_content'){
            $JArticle = new StileroAFBJarticle($article);
        }else if($option == 'com_k2'){
            $JArticle = new StileroAFBK2article($article);
        }
        $this->_JArticle = $JArticle->getArticleObj();
    }
    
    /**
     * Checks if a value is found in a list
     * @param string $commaSepList Comma separated list of values
     * @param string $needle The string to search for in the list
     * @return boolean true on success
     */
//    public static function isFoundInList($commaSepList, $needle){
//        $items = explode(",", $commaSepList);
//        if( (in_array($needle, $items))){
//            return TRUE;
//        }else{
//            return FALSE;
//        }
//    }
    
    /**
     * Checks if date A is newer than date B
     * @param date $dateA
     * @param date $dateB
     * @return boolean
     */
    public static function isANewerThanB($dateA, $dateB){
        if( ($dateA > $dateB)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Checks if all checks are passing before a post
     * @return boolean true if all checks are OK
     * @throws Exception On error
     */
    public function hasFullChecksPassed(){
        $isSuccessful = true;
        if(!StileroAFBServerRequirementHelper::hasCurlSupport()) {
            throw new Exception('TwitterTweet: Server Missing Curl support.'); 
            $isSuccessful = false;
        }
        if(!StileroAFBServerRequirementHelper::hasFileGetSupport()){
            throw new Exception('TwitterTweet: Server Missing Support for file_get_contents');
            $isSuccessful = false;
        } 
        if(!$this->_JArticle->isPublished) {
            throw new Exception('Aleady published');
            $isSuccessful = false;
        } 
        if(!$this->_JArticle->isPublic) {
            throw new Exception('Not public');
            $isSuccessful = false;
        } 
        if( (!self::isANewerThanB($this->_JArticle->publish_up, $this->_dateLimit)) && ($this->_dateLimit != '') ){
            throw new Exception('Too old');
            $isSuccessful = false;
        }
        if( !in_array($this->_JArticle->catid, $this->_categories) && !empty($this->_categories)){
            throw new Exception('Not a category to post');
            $isSuccessful = false;
        }
        if(!$this->_Table->isTableFound()){
            $this->_Table->createTable();
        }
        if( (!$this->_isOverridingDelayCheck)){
            if( $this->_Table->isTooEarly($this->_delay) ) {
                $message = JText::_(PlgSystemAutofbook::LANG_PREFIX.'TOO_EARLY');
                if($this->_isBackend){
                    StileroAFBMessageHelper::show($message, StileroAFBMessageHelper::TYPE_NOTICE);
                }
                $isSuccessful = false;
            }
            if( $this->_Table->isLogged($this->_JArticle->id, $this->_JArticle->component) ){
                $message = JText::_(PlgSystemAutofbook::LANG_PREFIX.'DUPLICATE_TWEET');
                if($this->_isBackend){
                    StileroAFBMessageHelper::show($message, StileroAFBMessageHelper::TYPE_NOTICE);
                }
                $isSuccessful = false;
            }
        }
        return $isSuccessful;
    }
    
    /**
     * Returns the article object
     * @return StileroFBJArticle Article object
     */
    public function getJArticle(){
        return $this->_JArticle;
    }
}
