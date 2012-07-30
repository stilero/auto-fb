<?php
/**
* Description of AutoFBook4
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-jul-29 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* This file is part of k2categories.
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
class k2categories{
    static function getCategories(){
        $db =& JFactory::getDBO();
        $query =
            'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('name').
                ' FROM '.$db->nameQuote('#__k2_categories').
                ' WHERE '.$db->nameQuote('published').'='.$db->quote('1').
                ' ORDER BY '.$db->nameQuote('name').' ASC';
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    static function selectList($id, $name, $selectedIDs, $isJ15=FALSE){
        $cats = $isJ15 ? self::getJ15Categories() : self::getCategories();
        if(!$cats){
            return '';
        }
        $htmlCode = '<select id="'.$id.'" name="'.$name.'[]" class="inputbox" multiple="multiple" size="10">';
        $defaultOption = array(
            array(
                'id' => '', 
                'name' => 'none')
            );
        $categories = array_merge($defaultOption, $cats);
        foreach ($categories as $category) {
            $selected = '';
            if(isset($selectedIDs) && $selectedIDs !=""){
                $selected = in_array($category['id'], $selectedIDs) ? ' selected="selected"': '';
            }
            $options.='<option value="'.$category['id'].'"'.$selected.'>'.$category['name'].'</option>'; 
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
    class JElementK2categories extends JElement{
        private $config;

        function fetchElement($name, $value, &$node, $control_name){
            $data = null;
            foreach ((Array)$this->form as $key => $val) {
                if($val instanceof JRegistry){
                $data = &$val;
                break;
                }
            }
            $data = $data->toArray();
            $selectedOptions = $data['params']['k2cats'];
            return k2categories::selectList($this->id, $this->name, $selectedOptions);
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
    class JFormFieldK2categories extends JFormField {
        protected $type = 'k2categories';
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
            $selectedOptions = $data['params']['k2cats'];
            return k2categories::selectList($this->id, $this->name, $selectedOptions);
        }
        
        protected function getLabel(){
            $toolTip = JText::_($this->element['description']);
            $text = JText::_($this->element['label']);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
        
    }//End Class
}