<?php
/**
* Description of AutoFBook4
*
* @version  1.1
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-21 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');
class fbPages{
    static function assetsPath(){
        $assetsPath = JURI::root().'media/plg_autofbook/';
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
    
    static function addJsGeneral(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'fbpages.js');
    }
    
    static function pagesList($id, $name=''){
        $htmlCode =
        '<select id="'.$id.'" name="'.$name.'">'.
        '<option selected="selected" value="me">'.JText::_('PLG_SYSTEM_AUTOFBOOK_ELEMENT_FBPAGES_PERSONAL').'</option>'.
        '</select>';
       
        return $htmlCode;
    }
    
    static function addJs16(){
        $document =& JFactory::getDocument();
        $document->addScript(self::jsPath().'j16Elements.js');
    }
    
    static function addTranslationJS(){
        $document =& JFactory::getDocument();
        $jsTranslationStrings = 'var PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS').'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_FAILURE = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_FAILURE').'";';
        $document->addScriptDeclaration($jsTranslationStrings);        
    }
}

    class JFormFieldFbpages extends JFormField {
        protected $type = 'fbpages';

        protected function getInput(){
            fbPages::addJs16();
            fbPages::addJsGeneral();
            fbPages::addTranslationJS();
            return fbPages::pagesList($this->id, $this->name);
        }
        
        protected function getLabel(){
            $toolTip = JText::_($this->element['description']);
            $text = JText::_($this->element['label']);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
        
    }//End Class
