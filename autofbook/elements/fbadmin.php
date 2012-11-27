<?php
/**
* Description of AutoFBook
*
* @version  1.1
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-21 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');
class fbAdmin{
    static function assetsPath(){
        $assetsPath = JURI::root().'media/plg_autofbook/';
        return $assetsPath;
    }
    
    static function addCssFile(){
        $cssPath = self::assetsPath().'css/settings.css';
        $document =& JFactory::getDocument();
        $document->addStyleSheet($cssPath);
    }
    
    static function imgPath(){
        $imgPath = self::assetsPath().'images/';
        return $imgPath;
    }
    
    static function jsPath(){
        $imgPath = self::assetsPath().'js/';
        return $imgPath;
    }
    
    static function addJsGeneral(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'fbadmin.js');
    }
    
    static function authorizedAdmin($id){
        $htmlCode =
        '<span id="'.$id.'" class="readonly">'.
        JText::_('PLG_SYSTEM_AUTOFBOOK_ELEMENT_FBADMIN_NOT_AUTHORIZED').
        '</span>'.
        self::loaderImage($id);
        return $htmlCode;
    }
    
    static function loaderImage($id){
        $imgHTML = '<span id="'.$id.'_loader" class="readonly"><img src="'.self::imgPath().'ajax-loader.gif" width="16" height="11"></span>';
        return ;
    }
    
    static function addJs16(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'j16Elements.js');
    }
    
    static function addTranslationJS(){
        $document =& JFactory::getDocument();
        $jsTranslationStrings = 'var PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS').'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_FAILURE = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_FAILURE').'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED').'"';
        $document->addScriptDeclaration($jsTranslationStrings);        
    }
}


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
        $toolTip = JText::_($this->element['description']);
        $text = JText::_($this->element['label']);
        $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
        return $labelHTML;
    }

}//End Class
