<?php
/**
 * Extended read permissions
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

class StileroFBPermissionsExtendedRead extends StileroFBPermissions{
    
    const FRIENDLISTS = 'read_friendlists'; //Provides access to any friend lists the user created. All user's friends are provided as part of basic data, this extended permission grants access to the lists of friends a user has created, and should only be requested if your application utilizes lists of friends.
    const INSIGHTS = 'read_insights'; //Provides read access to the Insights data for pages, applications, and domains the user owns.
    const MAILBOX = 'read_mailbox'; //Provides the ability to read from a user's Facebook Inbox.
    const REQUESTS = 'read_requests'; //Provides read access to the user's friend requests
    const STREAM = 'read_stream'; //Provides access to all the posts in the user's News Feed and enables your application to perform searches against the user's News Feed
    const XMPP_LOGIN = 'xmpp_login'; //Provides applications that integrate with Facebook Chat the ability to log in users.
    const USER_ONLINE_PRESENCE = 'user_online_presence'; //Provides access to the user's online/offline presence
    const FRIENDS_ONLINE_PRESENCE = 'friends_online_presence'; //Provides access to the user's friend's online/offline presence
}
