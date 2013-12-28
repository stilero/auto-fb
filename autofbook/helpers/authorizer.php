<?php
/**
 * Authorizing methods - Contacts FB and exchanges tokens
 * @version 1.1
 * @package AutoFBook Plugin
 * @author    Daniel Eliasson Stilero AB - http://www.stilero.com
 * @copyright	Copyright (c) 2011 Stilero AB. All rights reserved.
 * @license	GPLv2
 * 
*/
// no direct access
define('_JEXEC', 1); 
if(!defined('DS')){
    define('DS', DIRECTORY_SEPARATOR);
}
define('PATH_FBLIBRARY_FBOAUTH', '..'.DS.'..'.DS.'library'.DS.'fblibrary'.DS.'fboauth'.DS);
define('PATH_FBLIBRARY_OAUTH', '..'.DS.'..'.DS.'library'.DS.'fblibrary'.DS.'oauth'.DS);
require_once PATH_FBLIBRARY_OAUTH.'communicator.php';
require_once PATH_FBLIBRARY_OAUTH.'client.php';
require_once PATH_FBLIBRARY_FBOAUTH.'accesstoken.php';
require_once PATH_FBLIBRARY_FBOAUTH.'code.php';
require_once PATH_FBLIBRARY_FBOAUTH.'app.php';
require_once PATH_FBLIBRARY_FBOAUTH.'jerror.php';
require_once PATH_FBLIBRARY_FBOAUTH.'response.php';
$appID = StileroFBOauthCode::sanitizeInt($_POST['client_id']);
$appSecret = StileroFBOauthCode::sanitizeString($_POST['client_secret']);
$code = StileroFBOauthCode::sanitizeString($_POST['code']);
$redirectURI = StileroFBOauthCode::sanitizeUrl($_POST['redirect_uri']);
$FBApp = new StileroFBOauthApp($appID, $appSecret);
$AccessToken = new StileroFBOauthAccesstoken($FBApp);
$json = $AccessToken->getTokenFromCode($code, $redirectURI);
$response = StileroFBOauthResponse::handle($json);
$AccessToken->tokenFromResponse($response);
$token = $AccessToken->token;
if($AccessToken->isShortTerm($token)){
    $json = $AccessToken->extend();
    $response = StileroFBOauthResponse::handle($json);
    $AccessToken->tokenFromResponse($response);
    $token = $AccessToken->token;
}
$jsonResponse = <<<EOD
{
   "access_token": "$token"
}
EOD;
    print $jsonResponse;
?>