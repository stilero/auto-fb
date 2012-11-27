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
class Register{
    static function getHTML($id){
        $htmlCode = '<span class="readonly"><a href=\'https://developers.facebook.com/setup\' id=\''.$id.'\' target=\'_blank\'>Create A Facebook App</a></span>';
        return $htmlCode;
    }
}


    class JFormFieldRegister extends JFormField {
        protected $type = 'register';

        protected function getInput(){
            return Register::getHTML($this->id);
        }
        
        protected function getLabel(){
            $toolTip = JText::_($this->element['description']);
            $text = JText::_($this->element['label']);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
    }//End Class
