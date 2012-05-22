<?php
/**
* Description of AutoFBook
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-20 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* This file is part of fbauthorize.
*
* AutoFBook4 is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* AutoFBook4 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with AutoFBook4.  If not, see <http://www.gnu.org/licenses/>.
*
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class fbauthorize{
    //var $assetsPath = JPATH_ROOT.DS.'media'.DS.'plg_autofbook';
    /*var $imgPath = self::$assetsPath.DS.'images';
    var $jsPath = self::$assetsPath.DS.'js';*/
    
    static function assetsPath(){
        $assetsPath = JURI::root(true).DS.'media'.DS.'plg_autofbook'.DS;
        return $assetsPath;
    }
    
    static function imgPath(){
        $imgPath = self::assetsPath().'images'.DS;
        return $imgPath;
    }
    
    static function jsPath(){
        $imgPath = self::assetsPath().'js'.DS;
        return $imgPath;
    }
    
    static function addJsGeneral(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'authorize.js');
    }
    
    static function addJs15(){
        $catcherURI = JURI::root().'plugins'.DS.'system'.DS.'autofbook'.DS.'helpers'.DS.'catcherJ15.php';
        $helpersURI = JURI::root().'plugins'.DS.'system'.DS.'autofbook'.DS.'helpers'.DS;
$jsVars = <<<EOD
    var helpersURI = '$helpersURI';
    var catcherURI = '$catcherURI';
EOD;
    $document =& JFactory::getDocument();
    $document->addScriptDeclaration($jsVars);
    $document->addScript('https://ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js');
    $document->addScript(self::jsPath().'j15Elements.js');
}

    static function addJs16(){
        $catcherURI = JURI::root().'plugins'.DS.'system'.DS.'autofbook'.DS.'autofbook'.DS.'helpers'.DS.'catcherJ16.php';
        //$catcherURI = urlencode($catcherURI);
        $helpersURI = JURI::root().'plugins'.DS.'system'.DS.'autofbook'.DS.'autofbook'.DS.'helpers'.DS;
        //$helpersURI = urlencode($helpersURI);
        
$jsVars = <<<EOD
    var helpersURI = '$helpersURI';
    var catcherURI = '$catcherURI';
EOD;
    $document =& JFactory::getDocument();
    $document->addScriptDeclaration($jsVars);
    $document->addScript(self::jsPath().'j16Elements.js');
}

    static function connectButton($id){
        $buttonImage = self::imgPath().'connect-button.png';
        $htmlCode = 
            '<span class="readonly">'.
            '<a '.
            'id="'.$id.'" '.
            'class="fbconnect" '.
            'title="'.JText::_('MOD_INSTAGRAM_AUTHORIZE').'" '.
            'href="'.$link.'" '.
            'target="_blank" >'.
            //'<img src="'.$buttonImage.'" />'.
            'Connect to FB'.
            '</a>'.
            '</span>';
        return $htmlCode;
    }
    
    static function addTranslationJS(){
        $document =& JFactory::getDocument();
        $jsTranslationStrings = 'var PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS = "'.JText::_(PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS).'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_FAILURE = "'.JText::_(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE).'";';
        $document->addScriptDeclaration($jsTranslationStrings);        
    }
    
}

if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementAuthorize extends JElement{

        function fetchElement($name, $value, &$node, $control_name){
            fbauthorize::addJs15();
            fbauthorize::addJsGeneral();
            fbauthorize::addTranslationJS();
            return fbauthorize::connectButton($control_name.$name);
        }
    }//End Class J1.5
}else{
    /**
    * @since J1.6
    */
    class JFormFieldAuthorize extends JFormField {
        protected $type = 'authorize';
        private $config;

        protected function getInput(){
            fbauthorize::addJs16();
            fbauthorize::addJsGeneral();
            fbauthorize::addTranslationJS();
            return fbauthorize::connectButton($this->id);
        }
        
        protected function getLabel(){
            $toolTip = JText::_('');
            $text = JText::_('');
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
    }//End Class
}