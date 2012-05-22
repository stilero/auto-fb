<?php
/**
 * A Class for doing necessary checks before sharing to social services
 *
 * @version $Id: jfbClass.php 21 2012-02-05 16:25:19Z webbochsant@gmail.com $
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-22 Stilero AB
 * @license GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of AutoFBook
 * 
 * AutoFBook is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or 
 * (at your option) any later version.
 * 
 * AutoFBook is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with AutoFBook.  
 * If not, see <http://www.gnu.org/licenses/>.
 */
class JFBClass extends FBookClass{
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
