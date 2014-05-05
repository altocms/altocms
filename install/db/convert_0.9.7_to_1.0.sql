-- --------------------------------------------------------
-- Patch from 0.9.7.x upto 1.0.0

-- --------------------------------------------------------

--
-- Структура таблицы `prefix_blog_type`
--

CREATE TABLE IF NOT EXISTS `prefix_blog_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_code` varchar(16) NOT NULL,
  `type_name` varchar(32) NOT NULL,
  `type_description` varchar(255) DEFAULT NULL,
  `allow_add` tinyint(1) DEFAULT '1',
  `min_rate_add` float DEFAULT '0',
  `allow_list` tinyint(1) DEFAULT '1',
  `min_rate_list` float DEFAULT NULL,
  `index_ignore` tinyint(1) DEFAULT '0',
  `membership` tinyint(4) DEFAULT '0',
  `acl_write` int(11) DEFAULT NULL,
  `min_rate_write` float DEFAULT '0',
  `acl_read` int(11) DEFAULT NULL,
  `min_rate_read` float DEFAULT '0',
  `acl_comment` int(11) DEFAULT NULL,
  `min_rate_comment` float DEFAULT '0',
  `content_type` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `norder` int(11) DEFAULT '0',
  `candelete` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`type_code`),
  KEY `numord` (`norder`),
  KEY `allow_add` (`allow_add`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Дамп данных таблицы `prefix_blog_type`
--

INSERT INTO `prefix_blog_type` (`id`, `type_code`, `type_name`, `type_description`, `allow_add`, `min_rate_add`, `allow_list`, `min_rate_list`, `index_ignore`, `membership`, `acl_write`, `min_rate_write`, `acl_read`, `min_rate_read`, `acl_comment`, `min_rate_comment`, `content_type`, `active`, `norder`, `candelete`) VALUES
(1, 'personal', '{{blogtypes_type_personal_name}}', '{{blogtypes_type_personal_description}}', 0, 0, 1, NULL, 0, 0, 0, 0, 1, 0, 2, -10, '', 1, 0, 0),
(2, 'open', '{{blogtypes_type_open_name}}', '{{blogtypes_type_open_description}}', 1, 1, 1, NULL, 0, 1, 2, -10, 1, 0, 2, -10, NULL, 1, 0, 0),
(3, 'close', '{{blogtypes_type_close_name}}', '{{blogtypes_type_close_description}}', 1, 1, 1, NULL, 1, 2, 4, 0, 4, 0, 4, -10, NULL, 1, 0, 0),
(4, 'hidden', '{{blogtypes_type_hidden_name}}', '{{blogtypes_type_hidden_description}}', 0, 10, 0, NULL, 1, 4, 4, 0, 4, 0, 4, -10, NULL, 1, 0, 0);

-- --------------------------------------------------------

-- --
-- Структура таблицы 'prefix_mresource'
--

CREATE TABLE IF NOT EXISTS `prefix_mresource` (
  `mresource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_add` datetime NOT NULL,
  `date_del` datetime DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `storage` varchar(16) DEFAULT NULL,
  `uuid` varchar(64) NOT NULL,
  `link` tinyint(1) NOT NULL,
  `type` int(11) NOT NULL,
  `path_url` varchar(512) NOT NULL,
  `path_file` varchar(512) DEFAULT NULL,
  `hash_url` varchar(64) DEFAULT NULL,
  `hash_file` varchar(64) DEFAULT NULL,
  `candelete` tinyint(1) DEFAULT '1',
  `params` text,
  PRIMARY KEY (`mresource_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `hash_file` (`hash_file`),
  KEY `hash_url` (`hash_url`),
  KEY `link` (`link`),
  KEY `storage` (`storage`),
  KEY `uuid` (`uuid`)
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
  `date_add` datetime NOT NULL,
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

-- --------------------------------------------------------

ALTER TABLE `prefix_topic` ADD `topic_index_ignore` TINYINT( 2 ) NULL DEFAULT '0',
ADD INDEX ( `topic_index_ignore` );

ALTER TABLE `prefix_topic_photo` ADD `date_add` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
ADD INDEX ( `date_add` );

ALTER TABLE  `prefix_blog_user` ADD `blog_user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE  `prefix_vote` DROP PRIMARY KEY;
ALTER TABLE  `prefix_vote` ADD  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE  `prefix_vote` ADD INDEX  `target_id_type` (  `target_id` ,  `target_type` );
