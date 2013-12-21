<?php
/**
 * class-oauth-fb
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

class StileroFBPermissionsOpenGraphFriends extends StileroFBPermissions{
    
   const ACTIONS_MUSIC = 'friends_actions.music'; //Allows you to retrieve the actions published by all applications using the built-in music.listens action.
    const ACTIONS_NEWS = 'friends_actions.news'; //Allows you to retrieve the actions published by all applications using the built-in news.reads action.
    const ACTIONS_VIDEO = 'friends_actions.video'; //llows you to retrieve the actions published by all applications using the built-in video.watches action.
    const GAMES_ACTIVITY = 'friends_games_activity'; //Allows you post and retrieve game achievement activity.
}
