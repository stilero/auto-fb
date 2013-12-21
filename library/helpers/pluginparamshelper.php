<?php
/**
 * Helper Class for storing and reading plugin params
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_autofbook
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-20 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBPluginparamshelper{
    
    /**
     * Stores Plugin params to the DB. Used after setting new param values.
     * @param stdClass $params the Joomla params object.  
     * Typically you would send $this->params from the plugin class to this method, 
     * after setting new values with $this->params->set('param_name', $value);
     * @param string $element The plugin element in small caps without 
     * plgSystem, for example the plugin plgSystemAutofbook would be autofbook. 
     * Check the db for correct name
     */
    public static function storeParams($params, $element){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update('#__extensions');
        $query->set('params = '.$db->quote($params->toString()));
        $query->where('element = '.$db->quote($element));
        $db->setQuery($query);
        $db->query();        
    }
}
