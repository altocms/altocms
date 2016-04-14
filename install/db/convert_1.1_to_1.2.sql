ALTER TABLE  `prefix_comment` ADD  `comment_extra` TEXT NULL DEFAULT NULL ;
ALTER TABLE  `prefix_user` ADD  `user_extra` TEXT NULL DEFAULT NULL ;
ALTER TABLE  `prefix_blog` ADD  `blog_extra` TEXT NULL DEFAULT NULL ;

-- META DATA
CREATE TABLE IF NOT EXISTS `prefix_meta_set` (
  `meta_id` int(11) NOT NULL AUTO_INCREMENT,
  `target_type` varchar(50) NOT NULL,
  `meta_name` varchar(50) NOT NULL,
  `meta_sort` int(11) NOT NULL DEFAULT '0',
  `meta_active` int(1) NOT NULL DEFAULT '1',
  `meta_plugin` varchar(100) NOT NULL,
  `meta_type` varchar(16) NOT NULL DEFAULT 'input',
  `meta_title` varchar(50) NOT NULL,
  `meta_description` varchar(200) NOT NULL,
  `meta_options` text,
  `meta_required` tinyint(1) NOT NULL DEFAULT '0',
  `meta_postfix` text,

  PRIMARY KEY (`meta_id`),
  KEY `target_type` (`target_type`),
  KEY `meta_name` (`meta_name`),
  KEY `meta_sort` (`meta_sort`),
  KEY `meta_active` (`meta_active`),
  KEY `meta_plugin` (`meta_plugin`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `prefix_meta_val` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) unsigned NOT NULL,
  `meta_name` varchar(50) NOT NULL,
  `meta_lang` char(2) DEFAULT NULL,
  `meta_type` varchar(16) NOT NULL DEFAULT 'input',
  `value_type` enum('string','text','int', 'float', 'decimal', 'select', 'multi', 'bool', 'media') NOT NULL,
  `value_int` int(11) DEFAULT NULL,
  `value_float` double DEFAULT NULL,
  `value_string` varchar(255) DEFAULT NULL,
  `value_text` text NOT NULL,
  `value_source` text NOT NULL,

  PRIMARY KEY (`id`),
  KEY `target_type` (`target_type`),
  KEY `target_id` (`target_id`),
  KEY `meta_name` (`meta_name`),
  KEY `meta_lang` (`meta_lang`),
  KEY `value_int` (`value_int`),
  KEY `value_float` (`value_float`),
  KEY `value_string` (`value_string`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

