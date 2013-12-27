<?php
/**
 * plg_autofbook
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_autofbook
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-27 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBQueue{
    
    protected $postType;
    protected static $_table = '#__autofbook_queue';
    const POSTTYPE_LINK = 'link';
    const POSTTYPE_ARTICLE = 'photo';
    
    /**
     * Checks if a post is already in the queue
     * @param string $posttype
     * @param string $url
     * @return boolean
     */
    protected function isQueued($posttype, $url){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from(self::$_table);
        $query->where('posttype='.$db->quote($posttype));
        $query->where('url='.$db->quote($url));
        $db->setQuery($query);
        $db->query();
        $result = $db->loadAssoc();
        if(!$result){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * 
     * @param int $id Item id
     * @param string $posttype Post type (link / photo)
     * @param int $catid
     * @param string $url
     * @param string $lang
     * @param string $component
     */
    public function addToQueue($id, $posttype, $url, $title, $description, $picture, $catid, $lang='*', $component=''){
        if(!$this->isQueued($posttype, $url)){
            return;
        }
        $date=JFactory::getDate();
        $data =new stdClass();
        $data->id = null;
        $data->posttype = $posttype;
        $data->url = $url;
        $data->title = $title;
        $data->description = $description;
        $data->picture = $picture;
        $data->article_id = (int)$id;
        $data->cat_id = (int)$catid;
        $data->component = $component;
        $data->date = $date->toSql(true);
        $data->language = $lang;
        $db = JFactory::getDbo();
        $db->insertObject( self::$_table , $data, $id);
    }
    
    /**
     * Removes from queue
     * @param string $posttype The post type (link / photo)
     * @param string $url The url to be posted
     */
    public function removeFromQueue($posttype, $url){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete(self::$_table);
        $query->where('posttype='.$db->quote($posttype));
        $query->where('url='.$db->quote($url));
        $db->setQuery($query);
        $db->query();
    }
    
    /**
     * Returns the next object in queue
     * @return stdClass Next object in queue
     */
    public function getNextInQueue(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from(self::$_table);
        $query->order('date ASC');
        $db->setQuery($query);
        $result = $db->loadAssoc();
        return $result;
    }
}
