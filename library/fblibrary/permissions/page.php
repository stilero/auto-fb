<?php
/**
 * Page Permissions
 *
 * @version  1.0
 * @package Stilero
 * @subpackage class-oauth-fb
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-okt-03 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroFBPermissionsPage extends StileroFBPermissions{
    
    const MANAGE_PAGES = 'manage_pages'; //Enables your application to retrieve access_tokens for Pages and Applications that the user administrates. 
    
}
