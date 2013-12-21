<?php
/**
 * Email
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

class StileroFBPermisisonsPagesGroupsUsers extends StileroFBPermissions{
    //Pages
    const MANAGE_PAGES = 'manage_pages'; //Enables your application to retrieve access_tokens for Pages and Applications that the user administrates. 
    //Groups
    const GROUPS = 'user_groups'; //Provides access to the list of groups the user is a member of as the groups connection
    //User
    const ABOUT_ME = 'user_about_me'; //Provides access to the "About Me" section of the profile in the about property
    const ACTIVITIES = 'user_activities'; // Provides access to the user's list of activities as the activities connection
    const CHECKINS = 'user_checkins'; //Provides read access to the authorized user's check-ins
    const EVENTS = 'user_events'; //Provides access to the list of events the user is attending 
    const LIKES = 'user_likes'; //Provides access to the list of all of the pages the user has liked as the likes connection
    const PHOTOS = 'user_photos'; //Provides access to the photos the user has uploaded, and photos the user has been tagged in
    const STATUS = 'user_status'; //Provides access to the user's status messages and checkins. Please see the documentation for the location_post table for information on how this permission may affect retrieval of information about the locations associated with posts.
    const VIDEOS = 'user_videos'; //Provides access to the videos the user has uploaded, and videos the user has been tagged in
    const WEBSITE = 'user_website'; //Provides access to the user's web site URL
    const CREATE_EVENT = 'create_event'; //nables your application to create and modify events on the user's behalf
    const PUBLISH_ACTIONS = 'publish_actions'; //Enables your app to post content, comments and likes to a user's stream and requires extra permissions from a person using your app. 
    const PUBLISH_STREAM = 'publish_stream'; //The publish_stream permission is required to post to a Facebook Page's timeline. For a Facebook User use publish_actions.
    
    public static function permissionList() {
        parent::permissionList();
    }
}
