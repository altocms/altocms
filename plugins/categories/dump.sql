CREATE TABLE IF NOT EXISTS `prefix_category` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_title` varchar(30) NOT NULL,
  `category_url` varchar(30) NOT NULL,
  `category_sort` int(11) NOT NULL DEFAULT '0',
  `category_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`),
  KEY `category_url` (`category_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `prefix_category_rel` (
  `rel_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned NOT NULL,
  `blog_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`rel_id`),
  KEY `category_id` (`category_id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;