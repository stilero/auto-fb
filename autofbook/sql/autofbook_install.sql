CREATE TABLE IF NOT EXISTS`#__autofbook_posted` (
  `id` int(11) NOT NULL auto_increment,
  `article_id` int(11) NOT NULL default 0,
  `cat_id` int(11) NOT NULL default 0,
  `articlelink` varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `language` char(7) NOT NULL default '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;
