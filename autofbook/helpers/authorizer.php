<?php
    require_once '../classes/fbookClass.php';
    $appID = filter_var($_POST['client_id'], FILTER_SANITIZE_NUMBER_INT);
    $appSecret = filter_var($_POST['client_secret'], FILTER_SANITIZE_STRING);
    $authCode = filter_var($_POST['code'], FILTER_SANITIZE_STRING);
    $redirectURI = filter_var($_POST['redirect_uri'], FILTER_SANITIZE_URL);
    $config = array(
        'redirectURI'   =>  $redirectURI
    );
    $fb = new FBookClass($appID, $appSecret, $config);
    $fb->setOauthCode($authCode);
    $response  = $fb->requestAccessTokenForApp();
    if($fb->hasErrorOccured()){
        $errCode = $fb->getErrorCode();
        $errDesc = $fb->getErrorMessage();
        $response = <<<EOD
{
   "error": {
      "code": "$errCode",
      "type": "OAuthException",
      "message": "$errDesc"
   }
}
EOD;
    } else{
        $parsedResp = parse_str($response);
        $response = <<<EOD
{
   "access_token": "$access_token",
   "expires": "$expires"
}
EOD;
    }
    print $response;
?>
