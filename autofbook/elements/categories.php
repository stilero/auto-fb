<?php
/**
* Description of AutoFBook4
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-jul-28 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* This file is part of categories.
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
class Categories{
    static function getCategories(){
        $db =& JFactory::getDBO();
        $query =
            'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('title').
                ' FROM '.$db->nameQuote('#__categories').
                ' WHERE '.$db->nameQuote('extension').'='.$db->quote('com_content').
                ' AND published = 1 ORDER BY '.$db->nameQuote('title').' ASC';
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    static function getJ15Categories(){
        $db =& JFactory::getDBO();
        $query =
            'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('title').
                ' FROM '.$db->nameQuote('#__categories').
                ' WHERE '.$db->nameQuote('published').' = 1 ORDER BY '.$db->nameQuote('title').' ASC';
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    static function selectList($id, $name, $selectedIDs, $isJ15=FALSE){
        $htmlCode = '<select id="'.$id.'" name="'.$name.'[]" class="inputbox" multiple="multiple" size="10">';
        $defaultOption = array(
            array(
                'id' => '', 
                'title' => 'none')
            );
        $cats = $isJ15 ? self::getJ15Categories() : self::getCategories();
        $categories = array_merge($defaultOption, $cats);
        $options = '';
        foreach ($categories as $category) {
            $selected = '';
            if(isset($selectedIDs) && $selectedIDs !=""){
                $selected = in_array($category['id'], $selectedIDs) ? ' selected="selected"': '';
            }
            $options.='<option value="'.$category['id'].'"'.$selected.'>'.$category['title'].'</option>'; 
        }
        $htmlCode .= $options;
        $htmlCode .= '</select>';      
        return $htmlCode;
    }
    
}
if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementCategories extends JElement{
        private $config;

        function fetchElement($name, $value, &$node, $control_name){
            $rawParams = $this->_parent->_raw;
            $params = explode("\n", $rawParams);
            $sectIDParams = explode('=', $params[0]);
            $sectIDs = explode('|',$sectIDParams[1]);
            return Categories::selectList($control_name.$name, $control_name.'['.$name.']', $sectIDs, true);
            $htmlCode = '<textarea  id="' . $control_name.$name . '" name="' . $control_name.'['.$name.']' . '" value="' . $value . '" rows="5" cols="30" ></textarea>';
            return $htmlCode;
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
    class JFormFieldCategories extends JFormField {
        protected $type = 'categories';
        private $config;

        protected function getInput(){
            $data = null;
            foreach ((Array)$this->form as $key => $val) {
                if($val instanceof JRegistry){
                $data = &$val;
                break;
                }
            }
            $data = $data->toArray();
            $selectedOptions = '';
            if(isset($data['params']['section_id'])){
                $selectedOptions = $data['params']['section_id'];
            }
            return Categories::selectList($this->id, $this->name, $selectedOptions);
            $htmlCode = '<select id="'.$this->id.'" name="'.$this->name.'[]" class="inputbox" multiple="multiple">';
            $defaultOption = array(
                array(
                    'id' => '', 
                    'title' => 'none')
                );
            $categories = array_merge($defaultOption, Categories::getCategories());
            $selected = $this->params;
            $data = null;
            foreach ((Array)$this->form as $key => $val) {
                if($val instanceof JRegistry){
                $data = &$val;
                break;
                }
            }
            $data = $data->toArray();
            $selectedOptions = $data['params']['section_id'];
            foreach ($categories as $category) {
                $selected = '';
                if(isset($selectedOptions)){
                    $selected = in_array($category['id'], $selectedOptions) ? ' selected="selected"': '';
                }
                $options.='<option value="'.$category['id'].'"'.$selected.'>'.$category['title'].'</option>'; 
            }
            $htmlCode .= $options;
            $htmlCode .= '</select>';      
            return $htmlCode;
        }
        
        protected function getLabel(){
            $toolTip = JText::_($this->element['description']);
            $text = JText::_($this->element['label']);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
        
    }//End Class
}