<?php
/**
* Description of AutoFBook
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-21 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* This file is part of fbadmin.
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
class fbAdmin{
    static function assetsPath(){
        $assetsPath = JURI::root(true).DS.'media'.DS.'plg_autofbook'.DS;
        return $assetsPath;
    }
    
    static function addCssFile(){
        $cssPath = self::assetsPath().'css'.DS.'settings.css';
        $document =& JFactory::getDocument();
        $document->addStyleSheet($cssPath);
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
        $document->addScript(self::jsPath().'fbadmin.js');
    }
    
    static function authorizedAdmin($id){
        $htmlCode =
        '<span id="'.$id.'" class="readonly">'.
        JText::_(PLG_SYSTEM_AUTOFBOOK_ELEMENT_FBADMIN_NOT_AUTHORIZED).
        '</span>'.
        self::loaderImage($id);
        return $htmlCode;
    }
    
    static function loaderImage($id){
        $imgHTML = '<span id="'.$id.'_loader" class="readonly"><img src="'.self::imgPath().'ajax-loader.gif" width="16" height="11"></span>';
        return ;
    }
    
    static function addJs15(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'j15Elements.js');
    }

    static function addJs16(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'j16Elements.js');
    }
    
    static function addTranslationJS(){
        $document =& JFactory::getDocument();
        $jsTranslationStrings = 'var PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS = "'.JText::_(PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS).'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_FAILURE = "'.JText::_(PLG_SYSTEM_AUTOFBOOK_JS_FAILURE).'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED = "'.JText::_(PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED).'"';
        $document->addScriptDeclaration($jsTranslationStrings);        
    }
}

if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementFbadmin extends JElement{
        private $config;

        function fetchElement($name, $value, &$node, $control_name){
            fbAdmin::addCssFile();
            fbAdmin::addJs15();
            fbAdmin::addJsGeneral();
            fbAdmin::addTranslationJS();
            return fbAdmin::authorizedAdmin($control_name.$name);
        }
        function fetchTooltip ( $label, $description, &$xmlElement, $control_name='', $name=''){
            
        }
    }//End Class J1.5
}else{
    /**
    * @since J1.6
    */
    class JFormFieldFbadmin extends JFormField {
        protected $type = 'fbadmin';

        protected function getInput(){
            fbAdmin::addCssFile();
            fbAdmin::addJs16();
            fbAdmin::addJsGeneral();
            fbAdmin::addTranslationJS();
            return fbAdmin::authorizedAdmin($this->id);
        }
        
        protected function getLabel(){
            $toolTip = JText::_(PLG_SYSTEM_AUTOFBOOK_ELEMENT_FBADMIN_TOOLTIP_DESC);
            $text = JText::_(PLG_SYSTEM_AUTOFBOOK_ELEMENT_FBADMIN_TOOLTIP_LABEL);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
        
    }//End Class
}