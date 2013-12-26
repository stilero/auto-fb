<?php
/**
 * IPTC Class for reading photo keywords
 *
 * @version  1.0
 * @package Stilero
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-dec-26 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroAFBIptc{
    protected $filename;
    protected $iptc;
    
    public $title;
    public $category;
    public $other_categories;
    public $keywords = array();
    public $instructions;
    public $creation_date;
    public $creation_time;
    public $digital_creation_date;
    public $digital_creation_time;
    public $author_name;
    public $author_title;
    public $author_city;
    public $author_adress;
    public $author_state;
    public $author_country_code;
    public $author_country_name;
    public $copyright;
    public $description;
    public $description_author;

    const CHARACTER_SET = '1#090';
    const KEY_TITLE = '2#005';
    const KEY_CATEGORY = '2#015';
    const KEY_OTHER_CATEGORIES = '2#020';
    const KEY_KEYWORDS = '2#025';
    const KEY_INSTRUCTIONS = '2#040';
    const KEY_CREATION_DATE = '2#055';
    const KEY_CREATION_TIME = '2#060';
    const KEY_DIGITAL_CREATION_DATE = '2#062';
    const KEY_DIGITAL_CREATION_TIME = '2#063';
    const KEY_AUTHOR_NAME = '2#080';
    const KEY_AUTHOR_TITLE = '2#085';
    const KEY_AUTHOR_CITY = '2#090';
    const KEY_AUTHOR_ADRESS = '2#092';
    const KEY_AUTHOR_STATE = '2#095';
    const KEY_AUTHOR_COUNTRY_CODE = '2#100';
    const KEY_AUTHOR_COUNTRY_NAME = '2#101';
    const KEY_COPYRIGHT = '2#116';
    const KEY_DESCRIPTION = '2#120';
    const KEY_DESCRIPTION_AUTHOR = '2#122';
    
    public function __construct($filename) {
        $this->filename = $filename;
        $this->readIptcData();
        $this->parseIptc();
    }
    
    /**
     * Method for quickly setting data
     * @param string $keyname Class keyname
     * @param string $key IPTC Key (self::KEY_DESCRIPTION)
     */
    protected function setKey($keyname, $iptcKey){
        if(isset($this->iptc[$iptcKey][0]) && count($this->iptc[$iptcKey])==1){
            $this->$keyname = utf8_decode($this->iptc[$iptcKey][0]);
        }else if(isset($this->iptc[$iptcKey])){
            $this->$keyname = $this->iptc[$iptcKey];
        }
    }
    
    /**
     * Parses the IPTC data and makes it accessible in the class
     * @return void
     */
    protected function parseIptc(){
        if(!isset($this->iptc)){
            return;
        }
        $this->setKey('title', self::KEY_TITLE);
        $this->setKey('category', self::KEY_CATEGORY);
        $this->setKey('other_categories', self::KEY_OTHER_CATEGORIES);
        $this->setKey('keywords', self::KEY_KEYWORDS);
        $this->setKey('instructions', self::KEY_INSTRUCTIONS);
        $this->setKey('creation_date', self::KEY_CREATION_DATE);
        $this->setKey('creation_time', self::KEY_CREATION_TIME);
        $this->setKey('digital_creation_date', self::KEY_DIGITAL_CREATION_DATE);
        $this->setKey('digital_creation_time', self::KEY_DIGITAL_CREATION_TIME);
        $this->setKey('author_name', self::KEY_AUTHOR_NAME);
        $this->setKey('author_title', self::KEY_AUTHOR_TITLE);
        $this->setKey('author_city', self::KEY_AUTHOR_CITY);
        $this->setKey('author_adress', self::KEY_AUTHOR_ADRESS);
        $this->setKey('author_state', self::KEY_AUTHOR_STATE);
        $this->setKey('author_country_code', self::KEY_AUTHOR_COUNTRY_CODE);
        $this->setKey('author_country_name', self::KEY_AUTHOR_COUNTRY_NAME);
        $this->setKey('copyright', self::KEY_COPYRIGHT);
        $this->setKey('description', self::KEY_DESCRIPTION);
        $this->setKey('description_author', self::KEY_DESCRIPTION_AUTHOR);
    }
    
    /**
     * Reads the IPTC data and stores it in the class
     */
    protected function readIptcData(){
        $size = getimagesize($this->filename, $info);
        $iptc=array();
        if(is_array($info)){
            $iptc = iptcparse($info['APP13']); 
        }
        $this->iptc = $iptc;    
    }
}
