<?php
/**
 * Helper Class for displaying Joomla messages
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-14 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBMessageHelper{
    
    const TYPE_MESSAGE = 'message';
    const TYPE_ERROR = 'error';
    const TYPE_NOTICE = 'notice';
    
    /**
     * Displays a Joomla message
     * @param string $message The message to display
     * @param string $type message type. Use the constants of this class for convenient.
     */
    public static function show($message, $type='message'){
        $app = JFactory::getApplication();
        $app->enqueueMessage($message, $type);
    }
    
    /**
     * Raises an error and halts if the halt flag is set
     * @param string $message
     * @throws Exception
     */
    public static function error($message){
        throw new Exception($message);
    }
}
