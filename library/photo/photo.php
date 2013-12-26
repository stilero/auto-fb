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
    
    protected $exif = array();
    protected $iptc = array();
    protected $filename;
    
    const FILETYPE_JPEG = 'image/jpeg';
    const FILETYPE_PNG = 'image/png';
    
    public function __construct($filename='') {
        $this->filename = $filename;
    }
    
    /**
     * Checks if the provided file is a photo
     * @param string $filename Full image filename, and not URL.
     * @return int returns the filetype and false if not a photo
     */
    public static function isPhoto($filename){
        return exif_imagetype($filename);
    }
    
    /**
     * Reads the EXIF data of a photo file.
     * @param string $filename Full image filename, and not URL.
     */
    protected function readExif(){
        $this->exif = exif_read_data($this->filename);
    }
    
    protected function readIptcData(){
        $size = getimagesize($this->filename, $info);
        $iptc=array();
        if(is_array($info)){
            $iptc = iptcparse($info['APP13']); 
        }
        $this->iptc = $iptc;
    }
    public function getIptc(){
        $this->readIptcData();
        return $this->iptc;
    }
    public function getExif(){
        $this->readExif();
        return $this->exif;
    }
}
