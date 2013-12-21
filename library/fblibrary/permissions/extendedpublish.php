<?php
/**
 * Write Permissions
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

class StileroFBPermissionsExtendedPublish extends StileroFBPermissions{
    
    const ADS_MANAGEMENT = 'ads_management'; //Provides the ability to manage ads and call the Facebook Ads API on behalf of a user.
    const CREATE_EVENT = 'create_event'; //nables your application to create and modify events on the user's behalf
    const MANAGE_FRIENDLISTS = 'manage_friendlists'; //Enables your app to create and edit the user's friend lists.
    const MANAGE_NOTIFICATIONS = 'manage_notifications'; //Enables your app to read notifications and mark them as read. Intended usage: This permission should be used to let users read and act on their notifications; it should not be used to for the purposes of modeling user behavior or data mining. Apps that misuse this permission may be banned from requesting it.
    const PUBLISH_ACTIONS = 'publish_actions'; //Enables your app to post content, comments and likes to a user's stream and requires extra permissions from a person using your app. 
    const PUBLISH_STREAM = 'publish_stream'; //The publish_stream permission is required to post to a Facebook Page's timeline. For a Facebook User use publish_actions.
    const RSVP_EVENT = 'rsvp_event'; //Enables your application to RSVP to events on the user's behalf
}
