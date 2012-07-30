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
        $assetsPath = JURI::root(true).'/media/plg_autofbook/';
        return $assetsPath;
    }
    
    static function imgPath(){
        $imgPath = self::assetsPath().'images/';
        return $imgPath;
    }
    
    static function jsPath(){
        $imgPath = self::assetsPath().'js/';
        return $imgPath;
    }
    
    static function addJsGeneral($isJ15 = FALSE){
        $document =& JFactory::getDocument();
        if(!$isJ15){
            $document->addScript(self::jsPath().'authorize.js');
        }else{
            $document->addScript(self::jsPath().'authorize15.js');
        }
    }
    
    static function addJs15(){
        $catcherURI = JURI::root().'plugins/system/autofbook/helpers/catcherJ15.php';
        $helpersURI = JURI::root().'plugins/system/autofbook/helpers/';
$jsVars = <<<EOD
    var helpersURI = '$helpersURI';
    var catcherURI = '$catcherURI';
EOD;
    $document =& JFactory::getDocument();
    $document->addScriptDeclaration($jsVars);
    //$document->addScript('https://ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js');
    $document->addScript(self::jsPath().'j15Elements.js');
}

    static function addJs16(){
        $catcherURI = JURI::root().'plugins/system/autofbook/autofbook/helpers/catcherJ16.php';
        $helpersURI = JURI::root().'plugins/system/autofbook/autofbook/helpers/';
        
$jsVars = <<<EOD
    var helpersURI = '$helpersURI';
    var catcherURI = '$catcherURI';
EOD;
    $document =& JFactory::getDocument();
    $document->addScriptDeclaration($jsVars);
    $document->addScript(self::jsPath().'j16Elements.js');
}

    static function connectButton($id, $isJ15=FALSE){
        $buttonImage = self::imgPath().'connect-button.png';
        $htmlCode = 
            '<a '.
            'id="'.$id.'" '.
            'class="fbconnect" '.
            'title="'.JText::_('MOD_INSTAGRAM_AUTHORIZE').'" '.
            'href="'.$link.'" '.
            'target="_blank" >'.
            //'<img src="'.$buttonImage.'" />'.
            'Connect to FB'.
            '</a>';
        if(!$isJ15){
            $htmlCode = '<span class="readonly">'.$htmlCode.'</span>';
        }
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
            fbauthorize::addJsGeneral(TRUE);
            fbauthorize::addTranslationJS();
            return fbauthorize::connectButton($control_name.$name, TRUE);
        }
        
        function fetchTooltip ( $label, $description, &$xmlElement, $control_name='', $name=''){
            $output = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
            if ($description) {
                    $output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
            } else {
                    $output .= '>';
            }
            $output .= JText::_( $label ).'</label>';
            return $output;    
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