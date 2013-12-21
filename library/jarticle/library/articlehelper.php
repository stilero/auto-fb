<?php
/**
 * Article Helper class with methods for retrieving alias and slug from articles
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author danieleliasson
 * @copyright  (C) 2013-aug-12 Expression company is undefined on line 9, column 30 in Templates/Joomla/name.php.
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBArticleHelper{
    
    /**
     * Returns article alias from article id
     * @param int $id article id
     * @return string Article alias
     */
    public static function aliasFromID($id){
        jimport( 'joomla.filter.output' );
        $db=  JFactory::getDbo();
        $query=$db->getQuery(true);
        $query->select('a.alias, a.title');
        $query->from('#__content AS a');
        $query->where('a.id='.(int)$id);
        $db->setQuery($query);
        $result = $db->loadObject();
        $alias = empty($result->alias) ? $result->title : $result->alias;
        $filteredAlias = JFilterOutput::stringURLSafe($alias);
        return $filteredAlias;
    }
    
    /**
     * Returns the Article Slug
     * @param int $id Article ID
     * @param string $alias Article Alias
     * @return string Article slug
     */
    public static function slug($id, $alias){
        $slug = $id.':'.$alias;
        return $slug;
    }
    
    /**
     * Returns the Article Slug from the ID
     * @param int $id Article ID
     * @return string Article slug
     */
    public static function slugFromId($id){
        return self::slug($id, self::aliasFromID($id));
    }
}
