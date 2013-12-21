<?php
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
    
        $token = <<<EOD
{
   "access_token": "$AccessToken->token"
}
EOD;
    print $token;
?>
