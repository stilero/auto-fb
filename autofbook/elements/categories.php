<?php
/**
* Description of AutoFBook4
*
* @version  1.1
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-jul-28 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');
class Categories{
    static function getCategories(){
        $db =& JFactory::getDBO();
        $query =& $db->getQuery(true);
        $query->select('id, title');
        $query->from($db->quoteName('#__categories'));
        $query->where('extension = '.$db->quote('com_content'));
        $query->where('published = 1');
        $query->order('title');
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
        $cats = self::getCategories();
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
