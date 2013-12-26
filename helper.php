<?php
/**
 * Helper Class
 * Convenient class for importing classes
 *
 * @version  1.0
 * @package Stilero
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-15 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
if(!defined('DS')){
    define('DS', DIRECTORY_SEPARATOR);
}
define('PATH_LIBRARY', dirname(__FILE__).DS.'library'.DS);

//FBOOK LIBRARY PATHS
define('PATH_LIBRARY_FBLIBRARY', PATH_LIBRARY.'fblibrary'.DS);
define('PATH_LIBRARY_FBLIBRARY_ENDPOINTS', PATH_LIBRARY_FBLIBRARY.'endpoints'.DS);
define('PATH_LIBRARY_FBLIBRARY_FBOAUTH', PATH_LIBRARY_FBLIBRARY.'fboauth'.DS);
define('PATH_LIBRARY_FBLIBRARY_OAUTH', PATH_LIBRARY_FBLIBRARY.'oauth'.DS);
define('PATH_LIBRARY_FBLIBRARY_PERMISSIONS', PATH_LIBRARY_FBLIBRARY.'permissions'.DS);

//HELPERS PATHS
define('PATH_LIBRARY_HELPERS', PATH_LIBRARY.'helpers'.DS);

//JARTICLE PATHS
define('PATH_LIBRARY_JARTICLE', PATH_LIBRARY.'jarticle'.DS);
define('PATH_LIBRARY_JARTICLE_LIBRARY', PATH_LIBRARY_JARTICLE.'library'.DS);

//OPEN GRAPH LIBRARY
define('PATH_LIBRARY_OGLIBRARY', PATH_LIBRARY.'opengraph'.DS);

//PHOTO PATHS
define('PATH_LIBRARY_PHOTO', PATH_LIBRARY.'photo'.DS);

class StileroAFBHelper{
   /**
     * Imports all classes used to the autoloader of Joomla
     */
    public static function importClasses(){
        
        //FBOOK LIBRARIES
        JLoader::discover('StileroFB', PATH_LIBRARY_FBLIBRARY);
        JLoader::discover('StileroFBEndpoint', PATH_LIBRARY_FBLIBRARY_ENDPOINTS);
        JLoader::discover('StileroFBOauth', PATH_LIBRARY_FBLIBRARY_FBOAUTH);
        JLoader::discover('StileroOauth', PATH_LIBRARY_FBLIBRARY_OAUTH);
        JLoader::discover('StileroFBPermisisons', PATH_LIBRARY_FBLIBRARY_PERMISSIONS);
        
        //HELPERS
        JLoader::discover('StileroAFB', PATH_LIBRARY_HELPERS);
        
        //JARTICLE LIBRARY
        //JLoader::discover('StileroAFB', PATH_LIBRARY_JARTICLE);
        JLoader::register('StileroAFBJarticle', PATH_LIBRARY_JARTICLE.'jarticle.php');
        JLoader::discover('StileroAFB', PATH_LIBRARY_JARTICLE_LIBRARY);
        
        //General Library
        JLoader::discover('StileroAFB', PATH_LIBRARY);
        
        //OPENGRAPH LIBRARIES
        JLoader::discover('StileroFB', PATH_LIBRARY_OGLIBRARY);
        
        //OPENGRAPH LIBRARIES
        JLoader::discover('StileroAFB', PATH_LIBRARY_PHOTO);
    }
}
