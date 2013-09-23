-- --------------------------------------------------------
-- Patch from 0.9.7.x upto 1.0.0 (part 2)

-- --------------------------------------------------------

ALTER TABLE `prefix_topic_photo` ADD `date_add` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
ADD INDEX ( `date_add` );

--
-- Структура таблицы 'prefix_mresource'
--

CREATE TABLE IF NOT EXISTS `prefix_mresource` (
  `mresource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_add` datetime NOT NULL,
  `date_del` datetime DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `link` tinyint(1) NOT NULL,
  `type` int(11) NOT NULL,
  `path_url` varchar(512) NOT NULL,
  `path_file` varchar(512) DEFAULT NULL,
  `hash_url` varchar(64) DEFAULT NULL,
  `hash_file` varchar(64) DEFAULT NULL,
  `candelete` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`mresource_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `hash_file` (`hash_file`),
  KEY `hash_url` (`hash_url`),
  KEY `link` (`link`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Структура таблицы 'prefix_mresource_target'
--

CREATE TABLE IF NOT EXISTS `prefix_mresource_target` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mresource_id` int(11) NOT NULL,
  `target_type` varchar(32) NOT NULL,
  `target_id` int(11) NOT NULL,
  `date_add` int(11) NOT NULL,
  `target_tmp` varchar(32) DEFAULT NULL,
  `description` text,
  `incount` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_id` (`target_type`,`target_id`,`mresource_id`),
  KEY `target_tmp` (`target_tmp`),
  KEY `target_type` (`target_type`),
  KEY `target_id` (`target_id`),
  KEY `mresource_id` (`mresource_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
