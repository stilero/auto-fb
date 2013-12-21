<?php
/**
 * Login Dialog Response types
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

class StileroFBLoginDialogResponseType{
    
    const CODE = 'code'; //response data is included as URL parameters and contains code parameter.
    const TOKEN = 'token'; //Response data is included as a URL fragment and contains an access token.
    const BOTH = 'code%20token'; //Response data is included as a URL fragment and contains both an access token and the code parameter.
}
