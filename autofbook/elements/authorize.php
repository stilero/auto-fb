<?php
/**
* Description of AutoFBook
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-20 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class fbauthorize{
    
    const ASSETS_PATH = 'media/plg_autofbook/';
    const IMG_PATH = 'media/plg_autofbook/images/';
    const JS_PATH = 'media/plg_autofbook/js/';
    const CATCHER_URI = 'plugins/system/autofbook/autofbook/helpers/catcherJ16.php';
    const HELPERS_URI = 'plugins/system/autofbook/autofbook/helpers/';
    const GENERAL_JS = 'authorize.js';
    const ELEMENTS_JS = 'j16Elements.js';
    const CONNECTBUTTON_IMG = 'connect-button.png';
    
    static function addJsGeneral(){
        $document = JFactory::getDocument();
        $scriptURL = JUri::root() . fbauthorize::JS_PATH . fbauthorize::GENERAL_JS;
        $document->addScript($scriptURL);
    }
    
    static function addJs16(){
        $catcherURI = JUri::root() . fbauthorize::CATCHER_URI;
        $helpersURI = JUri::root() . fbauthorize::HELPERS_URI;
        
$jsVars = <<<EOD
    var helpersURI = '$helpersURI';
    var catcherURI = '$catcherURI';
EOD;
    $document = JFactory::getDocument();
    $document->addScriptDeclaration($jsVars);
    $document->addScript( JUri::root() . fbauthorize::JS_PATH . fbauthorize::ELEMENTS_JS );
}
    
    /**
     * Returns the HTML for the connect button
     * @param integer $id
     * @return string HTML
     */
    static function connectButton($id){
        $buttonImage = JUri::root() . fbauthorize::IMG_PATH . fbauthorize::CONNECTBUTTON_IMG;
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
    
    /**
     * Adds translation strings JS to the HTML
     */
    static function addTranslationJS(){
        $document = JFactory::getDocument();
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
