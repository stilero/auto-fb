<?php
/**
 * class Permission
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

class StileroFBPermissionExtendedProfileFriend extends StileroFBPermissions{
    
    const ABOUT_ME = 'friends_about_me'; //Provides access to the "About Me" section of the profile in the about property
    const ACTIVITIES = 'friends_activities'; // Provides access to the user's list of activities as the activities connection
    const BIRTHDAY = 'friends_birthday'; //Provides access to the birthday with year as the birthday property.
    const CHECKINS = 'friends_checkins'; //Provides read access to the authorized user's check-ins
    const EDUCATION_HISTORY = 'friends_education_history'; //Provides access to education history
    const EVENTS = 'friends_events'; //Provides access to the list of events the user is attending 
    const GROUPS = 'friends_groups'; //Provides access to the list of groups the user is a member of as the groups connection
    const HOMETOWN = 'friends_hometown'; //Provides access to the user's hometown in the hometown property
    const INTERESTS = 'friends_interests'; //Provides access to the user's list of interests as the interests connection
    const LIKES = 'friends_likes'; //Provides access to the list of all of the pages the user has liked as the likes connection
    const LOCATION = 'friends_location'; //Provides access to the user's current city as the location property
    const NOTES = 'friends_notes'; //Provides access to the user's notes as the notes connection
    const PHOTOS = 'friends_photos'; //Provides access to the photos the user has uploaded, and photos the user has been tagged in
    const QUESTIONS = 'friends_questions'; //Provides access to the questions the user or friend has asked
    const RELATIONSHIP = 'friends_relationships'; //Provides access to the user's family and personal relationships and relationship status
    const RELATIONSHIP_DETAILS = 'friends_relationship_details'; //Provides access to the user's relationship preferences
    const RELIGION_POLITICS = 'friends_religion_politics'; //Provides access to the user's religious and political affiliations
    const STATUS = 'friends_status'; //Provides access to the user's status messages and checkins. Please see the documentation for the location_post table for information on how this permission may affect retrieval of information about the locations associated with posts.
    const SUBSCRIPTIONS = 'friends_subscriptions'; //Provides access to the user's subscribers and subscribees
    const VIDEOS = 'friends_videos'; //Provides access to the videos the user has uploaded, and videos the user has been tagged in
    const WEBSITE = 'friends_website'; //Provides access to the user's web site URL
    const WORK_HISTORY = 'friends_work_history'; //Provides access to work history as the work property
    
}
