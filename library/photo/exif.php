<?php
/**
 * plg_autofbook
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_autofbook
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-26 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBExif{
    
    protected $exif = array();
    
    public function __construct($config="") {
        $defaultConfig = array(
            'configName' => 'configVal'
        );
        if(isset($config) && !empty($config)){
            $defaultConfig = array_merge($defaultConfig, $config);
        }
        $this->_config = $defaultConfig;
    }
}
