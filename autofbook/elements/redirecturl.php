<?php
/**
* Description of plg_autofbook
*
* @version  1.0
* @author Daniel Eliasson <daniel at stilero.com>
* @copyright  (C) 2014-jan-09 Stilero Webdesign (http://www.stilero.com)
* @category Custom Form field
* @license    GPLv2
*
*
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');


class JFormFieldRedirecturl extends JFormField {
    protected $type = 'redirecturl';

    /**
     * Static class for returnning selectlists.
     */

    protected function getInput(){
        $url = JURI::root().'plugins/system/autofbook/autofbook/helpers/catcherJ16.php';
        $htmlCode = '<input id="'.$this->id.'" name="'.$this->name.'" type="text" class="text_area" size="9" value="'.$url.'"/>';
        return $htmlCode;
    }

    protected function getLabel(){
        $toolTip = JText::_($this->element['description']);
        $text = JText::_($this->element['label']);
        $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
        return $labelHTML;
    }

}//End Class