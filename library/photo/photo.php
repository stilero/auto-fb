<?php
/**
 * Photo Class
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

class StileroAFBPhoto{
    
    protected $Exif;
    protected $Iptc;
    protected $filename;
    
    public function __construct($filename='') {
        $this->filename = $filename;
        $this->Exif = new StileroAFBExif($filename);
        $this->Iptc = new StileroAFBIptc($filename);
    }
    
    public function getExif(){
        return $this->Exif;
    }
    
    public function getIPTC(){
        return $this->Iptc;
    }
}
