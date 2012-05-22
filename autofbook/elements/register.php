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
* This file is part of register.
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
class Register{
    static function getHTML($id){
        $htmlCode = '<span class="readonly"><a href=\'https://developers.facebook.com/setup\' id=\''.$id.'\' target=\'_blank\'>Create A Facebook App</a></span>';
        return $htmlCode;
    }
}

if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementRegister extends JElement{
        private $config;

        function fetchElement($name, $value, &$node, $control_name){
            return Register::getHTML($control_name.$name);
        }
    }//End Class J1.5
}else{
    /**
    * @since J1.6
    */
    class JFormFieldRegister extends JFormField {
        protected $type = 'register';

        protected function getInput(){
            return Register::getHTML($this->id);
        }
        
        protected function getLabel(){
            $toolTip = JText::_(PLG_SYSTEM_AUTOFBOOK_ELEMENT_REGISTER_TOOLTIP_DESC);
            $label = JText::_(PLG_SYSTEM_AUTOFBOOK_ELEMENT_REGISTER_TOOLTIP_LABEL);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$label.'::'.$toolTip.'">'.$label.'</label>';
            return $labelHTML;
        }
    }//End Class
}