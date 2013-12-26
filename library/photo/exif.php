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
    protected $filename;
    
    public $camera_manufacturer;
    public $camera_model;
    public $camera_exposure_time;
    public $camera_aperture;
    public $camera_iso;
    public $camera_focal_length;
    public $camera_lens;
    public $image_description;
    public $image_software;
    public $image_datetime;
    public $image_author;
    public $image_copyright;
    
    const KEY_CAMERA_MANUFACTURER = 'Make';
    const KEY_CAMERA_MODEL = 'Model';
    const KEY_CAMERA_EXPOSURE_TIME = 'ExposureTime';
    const KEY_CAMERA_APERTURE = 'FNumber';
    const KEY_CAMERA_ISO = 'ISOSpeedRatings';
    const KEY_CAMERA_FOCAL_LENGTH = 'FocalLength';
    const KEY_CAMERA_LENS = 'UndefinedTag:0xA434';
    const KEY_IMAGE_DESCRIPTION = 'ImageDescription';
    const KEY_IMAGE_SOFTWARE = 'Software';
    const KEY_IMAGE_DATETIME = 'DateTime';
    const KEY_IMAGE_AUTHOR = 'Artist';
    const KEY_IMAGE_COPYRIGHT = 'Copyright';
    
    public function __construct($filename) {
        $this->filename = $filename;
        if($this->isPhoto()){
            $this->readExif();
            $this->parseExif();
        }else{
            print 'not photo';
        }
    }
    
    /**
     * Checks if the provided file is a photo
     * @param string $filename Full image filename, and not URL.
     * @return int returns the filetype and false if not a photo
     */
    public function isPhoto(){
        return exif_imagetype($this->filename);
    }
    
    /**
     * Method for quickly setting data
     * @param string $keyname Class keyname
     * @param string $key IPTC Key (self::KEY_DESCRIPTION)
     */
    protected function setKey($keyname, $exifkey){
        if(isset($this->exif[$exifkey])){
            $this->$keyname = $this->exif[$exifkey];
        }
    }
    
    /**
     * Parses the IPTC data and makes it accessible in the class
     * @return void
     */
    protected function parseExif(){
        if(!isset($this->exif)){
            return;
        }
        $this->setKey('image_description', self::KEY_IMAGE_DESCRIPTION);
        $this->setKey('camera_manufacturer', self::KEY_CAMERA_MANUFACTURER);
        $this->setKey('camera_model', self::KEY_CAMERA_MODEL);
        $this->setKey('camera_exposure_time', self::KEY_CAMERA_EXPOSURE_TIME);
        $this->setKey('camera_aperture', self::KEY_CAMERA_APERTURE);
        $this->setKey('camera_iso', self::KEY_CAMERA_ISO);
        $this->setKey('camera_focal_length', self::KEY_CAMERA_FOCAL_LENGTH);
        $this->setKey('camera_lens', self::KEY_CAMERA_LENS);
        $this->setKey('image_description', self::KEY_IMAGE_DESCRIPTION);
        $this->setKey('image_software', self::KEY_IMAGE_SOFTWARE);
        $this->setKey('image_datetime', self::KEY_IMAGE_DATETIME);
        $this->setKey('image_author', self::KEY_IMAGE_AUTHOR);
        $this->setKey('image_copyright', self::KEY_IMAGE_COPYRIGHT);
    }
    
    /**
     * Reads the EXIF data of a photo file.
     * @param string $filename Full image filename, and not URL.
     */
    protected function readExif(){
        $this->exif = exif_read_data($this->filename);
    }
    
    public function getExif(){
        return $this->exif;
    }
}
