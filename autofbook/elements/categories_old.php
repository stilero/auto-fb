<?php
/**
* Description of AutoFBook4
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-maj-22 Stilero Webdesign http://www.stilero.com
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
jimport('joomla.form.fields.list');
jimport('joomla.form.fields.category');
if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementCategories extends JElement{
        private $config;

        function fetchElement($name, $value, &$node, $control_name){
            $document =& JFactory::getDocument();
            $this->config = array(
                'jsAsset'       =>      'js/jsFile.js',
                'cssAsset'      =>      'assets/cssFile.css'
            );
            $base_path = JURI::root(true).'/plugins/content/';
            $document->addScript($base_path.$this->config['jsAsset']);
            $document->addStyleSheet($base_path.$this->config['cssAsset']);
            $htmlCode = '<textarea  id="' . $control_name.$name . '" name="' . $control_name.'['.$name.']' . '" value="' . $value . '" rows="5" cols="30" ></textarea>';
            return $htmlCode;
        }
        function fetchTooltip ( $label, $description, &$xmlElement, $control_name='', $name=''){
            
        }
    }//End Class J1.5
}else{
    /**
    * @since J1.6
    */
    class JFormFieldCategories extends JFormFieldList {
        protected $type = 'categories';

        protected function getInput(){
            
            return parent::getInput();
        }
        
        protected function getLabel(){
            $toolTip = JText::_('tooltip');
            $text = JText::_('tooltip text');
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return ;
        }
        
        protected function getOptions()
	{
		// Initialise variables.
		$options = array();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published = (string) $this->element['published'];
		$name = (string) $this->element['name'];

		// Load the category options for a given extension.
		if (!empty($extension)){
                    // Filter over published state or not depending upon if it is present.
                    if ($published){
                        $options = JHtml::_('category.options', $extension, array('filter.published' => explode(',', $published)));}
                    else{
                        $options = JHtml::_('category.options', $extension);
                    }
                    // Verify permissions.  If the action attribute is set, then we scan the options.
                    if ((string) $this->element['action']){
                        // Get the current user object.
                        $user = JFactory::getUser();

                        // For new items we want a list of categories you are allowed to create in.
                        if (!$this->form->getValue($name)){
                            foreach ($options as $i => $option) {
                                // To take save or create in a category you need to have create rights for that category
                                // unless the item is already in that category.
                                // Unset the option if the user isn't authorised for it. In this field assets are always categories.
                                if ($user->authorise('core.create', $extension . '.category.' . $option->value) != true )
                                {
                                    unset($options[$i]);
                                }
                            }
                        }
                        // If you have an existing category id things are more complex.
                        else{
                            $categoryOld = $this->form->getValue($name);
                            foreach ($options as $i => $option)
                            {
                                // If you are only allowed to edit in this category but not edit.state, you should not get any
                                // option to change the category.
                                if ($user->authorise('core.edit.state', $extension . '.category.' . $categoryOld) != true){
                                        if ($option->value != $categoryOld){
                                            unset($options[$i]);
                                        }
                                }
                                // However, if you can edit.state you can also move this to another category for which you have
                                // create permission and you should also still be able to save in the current category.
                                elseif
                                    (($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
                                    && $option->value != $categoryOld)
                                {
                                    unset($options[$i]);
                                }
                            }
                        }
                    }
                    if (isset($this->element['show_root'])){
                        array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
                    }
		}
		else
		{
			JError::raiseWarning(500, JText::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
    }//End Class
}