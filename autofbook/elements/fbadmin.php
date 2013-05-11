<?php
/**
* Description of AutoFBook
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-21 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Basic class for handling the JS,CSS and image files
 */
class fbAdmin{
    
    const ASSETS_PATH = 'media/plg_autofbook/';
    const IMAGE_PATH = 'media/plg_autofbook/images/';
    const JS_PATH = 'media/plg_autofbook/js/';
    const SETTINGS_CSS = 'media/plg_autofbook/css/settings.css';
    const FB_ADMIN_JS = 'fbadmin.js';
    const ELEMENTS_JS = 'j16Elements.js';
    
    
    /**
     * Adds the Settings CSS file to the HTML header
     */
    static function addCssFile(){
        $cssPath = JUri::root() . fbAdmin::SETTINGS_CSS;
        $document = JFactory::getDocument();
        $document->addStyleSheet($cssPath);
    }
    
    /**
     * Adds the general JS to the Document header
     */
    static function addJsGeneral(){
        $document = JFactory::getDocument();
        $document->addScript( JUri::root() . fbAdmin::JS_PATH . fbAdmin::FB_ADMIN_JS);
    }
    
    /**
     * Returns HTML code for the authorised admin
     * @param string $id
     * @return string HTML
     */
    static function authorizedAdmin($id){
        $htmlCode =
        '<span id="'.$id.'" class="readonly">'.
        JText::_('PLG_SYSTEM_AUTOFBOOK_ELEMENT_FBADMIN_NOT_AUTHORIZED').
        '</span>'.
        self::loaderImage($id);
        return $htmlCode;
    }
    
    /**
     * Returns the loader image HTML
     * @param string $id form element id
     * @return string HTML
     */
    static function loaderImage($id){
        $imgHTML = '<span id="'.$id.'_loader" class="readonly"><img src="'. JUri::root() . fbAdmin::IMAGE_PATH .'ajax-loader.gif" width="16" height="11"></span>';
        return $imgHTML;
    }
    
    /**
     * Adds general JS to the document header
     */
    static function addJs16(){
        $document = JFactory::getDocument();
        $document->addScript( JUri::root() . fbAdmin::JS_PATH . fbAdmin::ELEMENTS_JS);
    }
    
    /**
     * Inserts translated strings to the JS of the document
     */
    static function addTranslationJS(){
        $document = JFactory::getDocument();
        $jsTranslationStrings = 'var PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_SUCCESS').'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_FAILURE = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_FAILURE').'";';
        $jsTranslationStrings .= 'var PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED = "'.JText::_('PLG_SYSTEM_AUTOFBOOK_JS_NOT_AUTHORIZED').'"';
        $document->addScriptDeclaration($jsTranslationStrings);        
    }
}

/**
 * Class for customising the Form field
 */
class JFormFieldFbadmin extends JFormField {
    protected $type = 'fbadmin';

    /**
     * Returns the input as HTML
     * @return string HTML
     */
    protected function getInput(){
        fbAdmin::addCssFile();
        fbAdmin::addJs16();
        fbAdmin::addJsGeneral();
        fbAdmin::addTranslationJS();
        return fbAdmin::authorizedAdmin($this->id);
    }
    
    /**
     * Returns the form label as HTML
     * @return string HTML
     */
    protected function getLabel(){
        $toolTip = JText::_($this->element['description']);
        $text = JText::_($this->element['label']);
        $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
        return $labelHTML;
    }

}//End Class
