<?php
/**
 * A Class for doing necessary checks before sharing to social services
 *
 * @version 1.2
 * @author danieleliasson Stilero AB - http://www.stilero.com
 * @copyright 2011-dec-22 Stilero AB
 * @license GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of AutoFBook
 * 
 * AutoFBook is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or 
 * (at your option) any later version.
 * 
 * AutoFBook is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with AutoFBook.  
 * If not, see <http://www.gnu.org/licenses/>.
 */
class stlShareControllerClass {

    var $articleObject;
    var $config;
    var $error;
    
    function __construct($config) {
        $this->error = FALSE;
        $this->config = array_merge(  
            array(
            'shareLogTableName'     =>      '',
            'categoriesToShare'     =>      '',
            'shareDelay'            =>      '',
            ),
        $config
        );
    }
    
    public function prepareTables() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if( $this->isJoomla15() ) {
            if( ! $this->tableExists() ){
                if( $this->createTables() ){
                }else{
                    $this->error['message'] = $this->config['pluginLangPrefix']."TABLESFAILED";
                    $this->error['type'] = 'error';
                    return FALSE;
                }
            }
        }
    }      

    public function isArticleObjectIncluded() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        if ( ! $this->articleObject->id ) {
            $this->error['message'] = $this->config['pluginLangPrefix'].'NOT_OBJECT';
            $this->error['type'] = 'error';
            return FALSE;
        }
    }
    
//    public function isItemActive() {
//        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
//        if($this->error != FALSE){
//            return FALSE;
//        }
//        if (($this->articleObject->published != 1)){
//            $this->error['message'] = $this->config['pluginLangPrefix'].'NOTACTIVE';
//            $this->error['type'] = 'notice';
//            return FALSE;
//        }
//    }

    public function isItemPublished() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
//        $date = JFactory::getDate();
//        $currentDate = $date->toMySQL();
//        $itemPublishDate = $this->articleObject->publish_up;
//        if ( $itemPublishDate > $currentDate ){
//            $this->error['message'] = $this->config['pluginLangPrefix'].'NOTACTIVE';
//            $this->error['type'] = 'notice';
//            return FALSE;
//        }
        if(!$this->articleObject->isPublished){
            $this->error['message'] = $this->config['pluginLangPrefix'].'NOTACTIVE';
            $this->error['type'] = 'notice';
            return FALSE;
        }
          return $this->articleObject->isPublished;
    }

    public function isItemNewEnough() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        $postItemsNewerThanDate = $this->config['articlesNewerThan'];
        $itemPublishDate = $this->articleObject->publish_up;
        if( ( $itemPublishDate < $postItemsNewerThanDate) && $postItemsNewerThanDate !="" ){
            $this->error['message'] = $this->config['pluginLangPrefix'].'ITEM_OLD';
            $this->error['type'] = 'notice';
            return FALSE;
        }
    }
    
    public function isItemPublic() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
//        $publicAccessCode = ($this->isJoomla15())?0:1;
//        if( $this->articleObject->access != $publicAccessCode ){
//            $this->error['message'] = $this->config['pluginLangPrefix'].'RESTRICT';
//            $this->error['type'] = 'notice';
//            return FALSE;
//        }
        if( !$this->articleObject->isPublic ){
            $this->error['message'] = $this->config['pluginLangPrefix'].'RESTRICT';
            $this->error['type'] = 'notice';
            return FALSE;
        }
    }

    public function isCategoryToShare() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        $include = false;
        $exclude = false;
        if('inclOrExcl' == 0){
            $include = true;
        }else{
            $exclude = true;
        }
        
        if ( $this->config['categoriesToShare'] == "" ){
            return TRUE;
        }
        //$categories = explode(",", $this->config['categoriesToShare']);
        $categories = $this->config['categoriesToShare'];
        //$categoriesCount = count($categories);
        $itemCategID = $this->articleObject->catid;
        if($include){
            if ( !in_array( $itemCategID, $categories ) ){
                $this->error['message'] = $this->config['pluginLangPrefix'].'NOTSECTION';
                $this->error['type'] = 'notice';
                return FALSE;
            }
        }elseif ($exclude) {
            if ( in_array( $itemCategID, $categories ) ){
                $this->error['message'] = $this->config['pluginLangPrefix'].'NOTSECTION';
                $this->error['type'] = 'notice';
                return FALSE;
            }
        }
        
    }

    public function isSharingToEarly(){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        $delayInMinutes = ( !is_numeric($this->config['shareDelay']) || $this->config['shareDelay'] < 2 )?3:$this->config['shareDelay'];
        $currentDate=date("Y-m-d H:i:s");
        $query;
        $db		= JFactory::getDbo();
        if( $this->isJoomla16() || $this->isJoomla17() ) {
            $query	= $db->getQuery(true);
            $query->select('id');
            $query->from( $this->config['shareLogTableName'] );
            $query->where("date > SUBTIME('".$currentDate."','0 0:".$delayInMinutes.":0.0')");
        }  elseif($this->isJoomla15()) {
            $query = "SELECT "
                .$db->nameQuote('id').
                " FROM ".$db->nameQuote( $this->config['shareLogTableName'] ).
                " WHERE date > SUBTIME('".$currentDate."','0 0:".$delayInMinutes.":0.0')";
        }
        $db->setQuery($query);
        $postMadeWithinDelayTime = $db->loadObject();
        if($postMadeWithinDelayTime){
            $this->error['message'] = $this->config['pluginLangPrefix']."DELAYED";
            $this->error['type'] = 'notice';
            return TRUE;
        }
        return FALSE;
    }

    public function isItemAlreadyShared(){
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if($this->error != FALSE ){
            return FALSE;
        }
        $query;
        $db		= JFactory::getDbo();
        $articleID = $this->articleObject->id;
        if($this->isJoomla16() || $this->isJoomla17()) {
            $query	= $db->getQuery(true);
            $query->select('id');
            $query->from( $this->config['shareLogTableName'] );
            $query->where('article_id=' . $db->Quote($articleID));
        }  elseif($this->isJoomla15() ) {
            $query = 'SELECT '
            .$db->nameQuote('id').
                ' FROM '.$db->nameQuote( $this->config['shareLogTableName'] ).
                ' WHERE '.$db->nameQuote('article_id').'='.$db->Quote($articleID);
        }
        $db->setQuery($query);
        $itemAlreadyPosted = $db->loadObject();
        if($itemAlreadyPosted){
            $this->error['message'] = $this->config['pluginLangPrefix']."ALREADYSENT";
            $this->error['type'] = 'notice';
            return TRUE;
        }
        return FALSE;
    }
    
    public function isJoomla15() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if( version_compare(JVERSION,'1.5.0','ge') && version_compare(JVERSION,'1.6.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }

    public function isJoomla16() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if( version_compare(JVERSION,'1.6.0','ge') && version_compare(JVERSION,'1.7.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }

    public function isJoomla17() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        if(version_compare(JVERSION,'1.7.0','ge')) {
            return TRUE;
        }
        return FALSE;
    }

    public function createTables() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
            $dbObject		=& JFactory::getDbo();
            $queryDropTable = "DROP TABLE IF EXISTS `".$this->config['shareLogTableName']."`";
            $queryCreateTable = "CREATE TABLE `".$this->config['shareLogTableName']."` (
                    `id` int(11) NOT NULL auto_increment,
                    `article_id` int(11) NOT NULL default 0,
                    `cat_id` int(11) NOT NULL default 0,
                    `articlelink` varchar(255) NOT NULL default '',
                    `date` datetime NOT NULL default '0000-00-00 00:00:00',
                    `language` char(7) NOT NULL default '',
                    PRIMARY KEY  (`id`)
                    ) DEFAULT CHARSET=utf8;";
            $dbObject->setQuery($queryDropTable);
            $resultDropTable = $dbObject->query();
            $dbObject->setQuery($queryCreateTable);
            $resultCreateTable = $dbObject->query();
            if($resultCreateTable){
                return TRUE;
            }
            $this->error['message'] = $this->config['pluginLangPrefix']."CREATE_TABLE_FAILED";
            $this->error['type'] = 'error';
            return FALSE;
    }
 
    public function saveLogToDB() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $itemID = $this->articleObject->id;
        $itemCategID = $this->articleObject->catid;
        $itemLink = $this->articleObject->link;
        $itemLanguage = $this->articleObject->language;
        $date=&JFactory::getDate();
        $data =new stdClass();
        $data->id = null;
        $data->article_id = $itemID;
        $data->cat_id = $itemCategID;
        $data->articlelink = $itemLink;
        $data->date = date("Y-m-d H:i:s");
        $data->language = $itemLanguage;
        $db = &JFactory::getDbo();
        $db->insertObject( $this->config['shareLogTableName'] , $data, 'id');
        return;
    }
    
    public function deleteLogFromDB() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $db = &JFactory::getDbo();
        $itemID = $this->articleObject->id;
        $query = 'DELETE '.
            ' FROM '.$db->nameQuote($this->config['shareLogTableName']).
            ' WHERE '.$db->nameQuote('article_id').'='.$itemID;
        $db->setQuery($query);
        return $result = $db->query($query);
    }

    public function tableExists() {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $dbObject		=& JFactory::getDbo();
        $query = "DESC `".$this->config['shareLogTableName']."`";
        $dbObject->setQuery($query);
        $tableFound = $dbObject->query();
        if($tableFound){
            return TRUE;
        }
        return FALSE;
    }
    
    public function setArticleObjectFromJoomlaArticle($joomlaArticle) {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->articleObject = new stdClass();
        $this->articleObject->id = $joomlaArticle->id;        
        $this->articleObject->title = $joomlaArticle->title;
        $this->articleObject->link = "";
        $this->articleObject->catid = $joomlaArticle->catid;
        $this->articleObject->access = $joomlaArticle->access;
        $this->articleObject->pubplish_up = $joomlaArticle->pubplish_up;
        $this->articleObject->published = $joomlaArticle->state;
        $this->articleObject->language = ( $this->isJoomla15() ) ? "" : $joomlaArticle->language;
    }
    
    public function setArticleObject($articleObject) {
        if (JDEBUG) JError::raiseNotice( 0,__CLASS__."->".__FUNCTION__ );
        $this->articleObject = $articleObject;
    }
}

