<?php
/**
 * Helper class for checking if extensions are installed or not.
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

class StileroAFBExtensionHelper{
    
    //The different extension types of Joomla
    const TYPE_COMPONENT = 'component';
    const TYPE_MODULE = 'module';
    const TYPE_PLUGIN = 'plugin';
    const TYPE_FILE = 'file';
    const TYPE_LANGUAGE = 'language';
    const TYPE_LIBRARY = 'library';
    const TYPE_PACKAGE = 'package';
    const TYPE_TEMPLATE = 'template';
    
    /**
     * Checks if an extension is installed or not
     * @param string $option
     * @param string $type The type of extension, for example 'component', 'plugin' or 'module'. Use constants for options.
     * @return boolean True if installed, otherwise false
     */
    public static function isInstalled($option, $type='component'){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id AS id, element AS "option", params, enabled');
        $query->from('#__extensions');
        $query->where('type = '.$db->quote($type));
        $query->where('element = ' . $db->quote($option));
        $db->setQuery($query);
        $result = $db->loadObject();
        if($result == null){
            return FALSE;
        }
        return TRUE;
    }
}
