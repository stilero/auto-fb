<?php
/**
* Description of AutoFBook
*
* @version  1.1
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-20 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class fbauthorize{
    //var $assetsPath = JPATH_ROOT.DS.'media'.DS.'plg_autofbook';
    /*var $imgPath = self::$assetsPath.DS.'images';
    var $jsPath = self::$assetsPath.DS.'js';*/
    
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
        $document->addScript(self::jsPath().'authorize.js');
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

    static function connectButton($id){
        $buttonImage = self::imgPath().'connect-button.png';
        $htmlCode = 
            '<a '.
            'id="'.$id.'" '.
            'class="fbconnect" '.
            'title="Connect" '.
            'href="#" '.
            'target="_blank" >'.
            //'<img src="'.$buttonImage.'" />'.
            'Connect to FB'.
            '</a>';
       
        $htmlCode = '<span class="readonly">'.$htmlCode.'</span>';
        return $htmlCode;
    }
    
    static function addTranslationJS(){
        $document =& JFactory::getDocument();
        $jsTranslationStrings = 'var PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS').'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_FAILURE = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_FAILURE').'";';
        $document->addScriptDeclaration($jsTranslationStrings);        
    }
    
}


    class JFormFieldAuthorize extends JFormField {
        protected $type = 'authorize';

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
