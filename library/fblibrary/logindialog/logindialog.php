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

class StileroFBLoginDialog{
    
    private $FbApp;
    private $_redirectURL;
    private static $_url = 'https://www.facebook.com/dialog/oauth';
    
    /**
     * Class for generating Login Dialogs
     * @param StileroFBOauthApp $FbApp FB app object with ID and secret
     * @param string $redirectURL The URI to redirect to after dialog
     */
    public function __construct(StileroFBOauthApp $FbApp, $redirectURL) {
        $this->FbApp = $FbApp;
        $this->_redirectURL = $redirectURL;
    }
    
    /**
     * Returns the URL for the Login Dialog box
     * @param string $state An arbitrary unique string created by your app to guard against Cross-site Request Forgery
     * @param string $responseType Use login-dialog-response-type-class. Determines whether the response data included when the redirect back to the app occurs is in URL parameters or fragments.
     * @param string $scope Use FB-permission class. A comma separated list of Permissions to request from the person using your app.
     * @return string URL to Login Dialog
     */
    public function url($state='', $responseType='', $scope=''){
        $url = self::$_url.
                '?client_id='.$this->FbApp->id.
                '&redirect_uri='.$this->_redirectURL;
        if($state != ''){
            $url .= '&state='.$state;
        }
        if($responseType != ''){
            $url .= '&response_type='.$responseType;
        }
        if($scope != ''){
            $url .= '&scope='.$scope;
        }
        return $url;
    }
  
}
