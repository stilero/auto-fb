<?php
/**
* Description of AutoFBook4
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-jul-28 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * A Class for retrieveing the content categories
 */
class afbContentCategories{
    
    /**
     * Get all the content categories in an associate object list
     * @return Object
     */
    static function getCategories(){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, title');
        $query->from($db->quoteName('#__categories'));
        $query->where('extension = '.$db->quote('com_content'));
        $query->where('published = 1');
        $query->order('title');
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    /**
     * Returns the HTML code for a select list with all the categories
     * @param integer $id
     * @param string $name
     * @param Array $selectedIDs
     * @return string HTML code
     */
    static function selectList($id, $name, $selectedIDs){
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

    /**
     * Returns the HTML-code for the input
     * @return string HTML Code for the input
     */
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
        return afbContentCategories::selectList($this->id, $this->name, $selectedOptions);
        $htmlCode = '<select id="'.$this->id.'" name="'.$this->name.'[]" class="inputbox" multiple="multiple">';
        $defaultOption = array(
            array(
                'id' => '', 
                'title' => 'none')
            );
        $categories = array_merge($defaultOption, afbContentCategories::getCategories());
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

    /**
     * Returns the label for the categories in HTML format
     * @return string HTML code for the label
     */
    protected function getLabel(){
        $toolTip = JText::_($this->element['description']);
        $text = JText::_($this->element['label']);
        $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
        return $labelHTML;
    }

}//End Class
