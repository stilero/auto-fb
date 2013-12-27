DROP TABLE IF EXISTS `#__autofbook_log`;
CREATE TABLE `#__autofbook_log` (
  `id` int(11) NOT NULL auto_increment,
  `article_id` int(11) NOT NULL default 0,
  `cat_id` int(11) NOT NULL default 0,
  `articlelink` varchar(255) NOT NULL default '',
  `component` varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `language` char(7) NOT NULL default '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `#__autofbook_queue`;
CREATE TABLE `#__autofbook_queue` (
    `id` int(11) NOT NULL auto_increment,
    `posttype` varchar(255) NOT NULL default '',
    `url` varchar(255) NOT NULL default '',
    `title` varchar(255) NOT NULL default '',
    `description` varchar(255) NOT NULL default '',
    `picture` varchar(255) NOT NULL default '',
    `article_id` int(11) NOT NULL default 0,
    `cat_id` int(11) NOT NULL default 0,
    `component` varchar(255) NOT NULL default '',
    `date` datetime NOT NULL default '0000-00-00 00:00:00',
    `language` char(7) NOT NULL default '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

