<?php
/**
 * Permissions Interface
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

abstract class StileroFBPermissions{
    
    /**
     * Returns a comma separated list of all defined constants
     * @return string Permissions
     */
    public static function permissionList() {
        $permissions = array_values(get_defined_constants());
        $permissionList = implode(',', $permissions);
        return $permissionList;
    }
}