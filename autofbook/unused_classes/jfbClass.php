<?php
/**
 * A Class for doing necessary checks before sharing to social services
 *
 * @version $Id: jfbClass.php 21 2012-02-05 16:25:19Z webbochsant@gmail.com $
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-22 Stilero AB
 * @license GPLv2
 */
class afbJFBClass extends afbFBookClass{
    var $inBackend = false;
    
    function __construct($fbAppID, $fbAppSecret, $config="") {
        parent::__construct($fbAppID, $fbAppSecret, $config="");
        array(
                'redirectURI'          =>  '',
                'fbOauthToken'         =>  '',
                'fbPageID'              =>  ''
            );
        if(is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }
    
    protected function requestPermissionsForApp(){
        if(!$this->inBackend) {
            return;
        }
        $dialogURL = $this->getOAuthDialogURL();
        $app = JFactory::getApplication();
        $app->redirect( $dialogURL );
        return;
    }
}

?>
