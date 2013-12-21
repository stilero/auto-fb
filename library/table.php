<?php
/**
 * TwitterTweet Table class handles creation and updating of tables related to 
 * social share extensions
 *
 * @version  1.2
 * @package Stilero
 * @since J2.5
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-13 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBTable{
    
    protected $_table;
    
    /**
     * Handles creation and updating of tables related to social share extensions.
     * @param string $table the table name in Joomla style, for example '#__mytable"
     */
    public function __construct($table) {
        $this->_table = $table;
    }
    
    /**
     * Creates a table for social sharing
     * @return boolean true on success
     */
    public function createTable() {
            $db = JFactory::getDbo();
            $dropQuery = "DROP TABLE IF EXISTS `".$this->_table."`";
            $createQuery = "CREATE TABLE `".$this->_table."` (
                    `id` int(11) NOT NULL auto_increment,
                    `article_id` int(11) NOT NULL default 0,
                    `cat_id` int(11) NOT NULL default 0,
                    `articlelink` varchar(255) NOT NULL default '',
                    `component` varchar(255) NOT NULL default '',
                    `date` datetime NOT NULL default '0000-00-00 00:00:00',
                    `language` char(7) NOT NULL default '',
                    PRIMARY KEY  (`id`)
                    ) DEFAULT CHARSET=utf8;";
            $db->setQuery($dropQuery);
            $db->query();
            $db->setQuery($createQuery);
            $createResult = $db->query();
            if($createResult){
                return TRUE;
            }
            return FALSE;
    }
    
    /**
     * Checks if a table is found
     * @return boolean true if existing
     */
    public function isTableFound() {
        $db = JFactory::getDbo();
        $query = "DESC `".$this->_table."`";
        $db->setQuery($query);
        $result = $db->query();
        if($result){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Saves a log to the database. Typically called after sharing is done.
     * @param int $id article/item id
     * @param int $catid Category id
     * @param string $url Article/Item url
     * @param string $lang Language code string
     */
    public function saveLog($id, $catid, $url, $lang='*', $component='') {
        $date=JFactory::getDate();
        $data =new stdClass();
        $data->id = null;
        $data->article_id = (int)$id;
        $data->cat_id = (int)$catid;
        $data->articlelink = $url;
        $data->component = $component;
        $data->date = $date->toSql(true);
        //$data->date = date("Y-m-d H:i:s");
        $data->language = $lang;
        $db = JFactory::getDbo();
        $db->insertObject( $this->_table , $data, $id);
    }
    
    /**
     * Checks if a log is found for the article id provided
     * @param int $id Article id
     * @return boolean True if found
     */
    public function isLogged($id, $component){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from($this->_table);
        $query->where('article_id='.(int)$id);
        $query->where('component='.$db->quote($component));
        $db->setQuery($query);
        $result = $db->loadObject();
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Checks if posting to early
     * @param int $minutesBetweenPosts Number of minutes between posts
     * @return boolean true on success
     */
    public function isTooEarly($minutesBetweenPosts=5){
        if($minutesBetweenPosts > 60){
            $minutesBetweenPosts = 60;
        }
        $date = JFactory::getDate();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from($this->_table);
        $query->where("date > SUBTIME('".$date->toSql(true)."','0 0:".$minutesBetweenPosts.":0.0')");
        $db->setQuery($query);
        $result = $db->loadObject();
        if($result){
            return true;
        }else{
            return false;
        }
    }

}