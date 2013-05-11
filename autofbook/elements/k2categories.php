<?php
/**
* Description of AutoFBook4
*
* @version  1.2
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-jul-29 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

/**
 * A Class for retrieving K2 categories
 */
class afbK2Categories{
    
    /**
     * Checks if K2 is installed
     * @return boolean True if K2 is installed
     */
    static function isK2Installed(){
        // Ignore warnings because component may not be installed
        $warnHandlers = JERROR::getErrorHandling( E_WARNING );
        JERROR::setErrorHandling( E_WARNING, 'ignore' );

        // Check if component is installed
        if ( !JComponentHelper::isEnabled( 'com_k2', true) ) {
           return FALSE;
        }
        // Reset the warning handler(s)
        foreach( $warnHandlers as $mode ) {
           JERROR::setErrorHandling( E_WARNING, $mode );
        };
        return true;
    }
    
    /**
     * Get all the K2 Categories
     * @return Array List of categories
     */
    static function getCategories(){
        if(!self::isK2Installed()){
            return;
        }
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, name');
        $query->from($db->quoteName('#__k2_categories'));
        $query->where('published = 1');
        $query->order('name');
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    /**
     * Returns the HTML code for the select list
     * @param integer $id
     * @param string $name
     * @param Array $selectedIDs
     * @return string HTML code for the select list
     */
    static function selectList($id, $name, $selectedIDs){
        $cats = self::getCategories();
        if(!$cats){
            return '';
        }
        $htmlCode = '<select id="'.$id.'" name="'.$name.'[]" class="inputbox" multiple="multiple" size="10">';
        $defaultOption = array(
            array(
                'id' => '', 
                'name' => 'none')
            );
        $options = '';
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

/**
 * Class for custom form elements
 */
class JFormFieldK2categories extends JFormField {
    protected $type = 'k2categories';

    /**
     * Returns the HTML code for the input
     * @return string HTML code for the list
     */
    protected function getInput(){
        $data = null;
        foreach ((Array)$this->form as $key => $val) {
            if($val instanceof JRegistry){
            $data = &$val;
            break;
            }
        }
        $selectedOptions = '';
        $data = $data->toArray();
        if(isset($data['params']['k2cats'])){
            $selectedOptions = $data['params']['k2cats'];
        }
        return afbK2Categories::selectList($this->id, $this->name, $selectedOptions);
    }
    
    /**
     * Returns the HTML for the label
     * @return string HTML
     */
    protected function getLabel(){
        $toolTip = JText::_($this->element['description']);
        $text = JText::_($this->element['label']);
        $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
        return $labelHTML;
    }

}//End Class
