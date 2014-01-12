--
-- Database convertation from LS 1.0.3 to Alto CMS
--

ALTER TABLE `prefix_topic` MODIFY `topic_type` varchar(30) DEFAULT 'topic';
ALTER TABLE `prefix_comment` MODIFY `target_type` varchar(30) DEFAULT 'topic';
ALTER TABLE `prefix_comment_online` MODIFY `target_type` varchar(30) DEFAULT 'topic';
ALTER TABLE `prefix_favourite` MODIFY `target_type` varchar(30) DEFAULT 'topic';
ALTER TABLE `prefix_favourite_tag` MODIFY `target_type` varchar(30) DEFAULT 'topic';
ALTER TABLE `prefix_blog` MODIFY `blog_type` varchar(30) DEFAULT 'personal';
ALTER TABLE `prefix_vote` MODIFY `target_type` varchar(30) DEFAULT 'topic';
ALTER TABLE `prefix_topic` ADD `topic_date_show` datetime DEFAULT NULL AFTER `topic_date_edit`;
ALTER TABLE `prefix_subscribe` ADD `user_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL AFTER `target_id` ,
ADD INDEX ( `user_id` ) ;

UPDATE `prefix_topic` SET `topic_type`='topic' WHERE topic_type IN ('photoset', 'link', 'question');

CREATE TABLE IF NOT EXISTS `prefix_content` (
  `content_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `content_title` varchar(200) NOT NULL,
  `content_title_decl` varchar(200) NOT NULL,
  `content_sort` int(11) NOT NULL DEFAULT '0',
  `content_url` varchar(50) NOT NULL,
  `content_active` tinyint(1) NOT NULL DEFAULT '1',
  `content_candelete` tinyint(1) NOT NULL DEFAULT '0',
  `content_cancreate` tinyint(1) NOT NULL DEFAULT '0',
  `content_access` tinyint(1) NOT NULL DEFAULT '1',
  `content_config` text DEFAULT NULL,
  PRIMARY KEY (`content_id`),
  KEY ( `content_url` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `prefix_content` (`content_id`, `content_title`, `content_title_decl`, `content_sort`, `content_url`, `content_active`, `content_candelete`, `content_config`) VALUES
(1, 'Топик','Топики','1',  'topic', '1', '0', 'a:3:{s:8:"photoset";i:1;s:8:"question";i:1;s:4:"link";i:1;}');

CREATE TABLE IF NOT EXISTS `prefix_content_field` (
  `field_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `content_id` int(11) NOT NULL,
  `field_sort` int(11) NOT NULL DEFAULT '0',
  `field_type` varchar(30) NOT NULL DEFAULT 'input',
  `field_name` varchar(50) NOT NULL,
  `field_description` varchar(200) NOT NULL,
  `field_options` text DEFAULT NULL ,
  `field_required` tinyint(1) NOT NULL DEFAULT '0',
  `field_postfix` text DEFAULT NULL,
  PRIMARY KEY ( `field_id` ),
  KEY ( `content_id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prefix_content_values` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_id` int(11) UNSIGNED DEFAULT NULL,
  `target_type` varchar(40) NOT NULL DEFAULT 'topic',
  `field_id` int(11) NOT NULL,
  `field_type` varchar(40) NOT NULL DEFAULT 'input',
  `value_type` enum('number','string','text') NOT NULL DEFAULT 'string',
  `value` text NOT NULL,
  `value_varchar` varchar(250) DEFAULT NULL,
  `value_source` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `target_id` (`target_id`),
  KEY `target_type` (`target_type`),
  KEY `value_type` (`value_type`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `prefix_track` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_type` varchar(20) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_remove` datetime DEFAULT NULL,
  `ip` varchar(20) NOT NULL,
  `key` varchar(32) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type` (`target_type`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `key` (`key`),
  KEY `target_id` (`target_id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE  IF NOT EXISTS `prefix_adminban` (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `user_id` int(11) UNSIGNED NOT NULL,
  `banwarn` int(11) NOT NULL default '0',
  `bandate` datetime NOT NULL,
  `banline` datetime default NULL,
  `bancomment` varchar(255) default NULL,
  `banunlim` tinyint(1) NOT NULL default '0',
  `banactive` TINYINT DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prefix_adminips` (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `ip1` bigint(20) default NULL,
  `ip2` bigint(20) default '0',
  `bandate` datetime NOT NULL,
  `banline` datetime default NULL,
  `bancomment` varchar(255) default NULL,
  `banunlim` tinyint(1) NOT NULL default '0',
  `banactive` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`ip1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prefix_storage` (
  `storage_key` VARCHAR( 100 ) NOT NULL ,
  `storage_val` VARCHAR( 255 ) NULL ,
PRIMARY KEY ( `storage_key` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- Multisession & security

ALTER TABLE `prefix_session` DROP INDEX `user_id` ,
ADD INDEX `user_id` (`user_id`);
ALTER TABLE `prefix_session` CHANGE `session_key` `session_key` varchar(50) NOT NULL;
ALTER TABLE `prefix_session` ADD `session_agent_hash` varchar(50) DEFAULT NULL;
ALTER TABLE `prefix_session` ADD `session_exit` datetime DEFAULT NULL;
ALTER TABLE `prefix_user` ADD `user_last_session` varchar(50) DEFAULT NULL AFTER `user_date_comment_last`;

-- IPv6
ALTER TABLE `prefix_comment` CHANGE `comment_user_ip` `comment_user_ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_session` CHANGE `session_ip_create` `session_ip_create` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_session` CHANGE `session_ip_last` `session_ip_last` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_subscribe` CHANGE `ip` `ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_talk` CHANGE `talk_user_ip` `talk_user_ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_topic` CHANGE `topic_user_ip` `topic_user_ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_user` CHANGE `user_ip_register` `user_ip_register` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_vote` CHANGE `vote_ip` `vote_ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_wall` CHANGE `ip` `ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `prefix_track` CHANGE `ip` `ip` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- Edit comment
ALTER TABLE `prefix_comment` ADD `comment_user_edit` INT(11) UNSIGNED NULL DEFAULT '0' AFTER `comment_date`;
ALTER TABLE `prefix_comment` ADD `comment_date_edit` DATETIME NULL DEFAULT NULL AFTER `comment_date`;

-- v.0.9.7
ALTER TABLE `prefix_topic` ADD `topic_url` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
ADD INDEX ( `topic_url` );

-- Page table
CREATE TABLE IF NOT EXISTS `prefix_page` (
  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page_pid` int(11) unsigned DEFAULT NULL,
  `page_url` varchar(50) NOT NULL,
  `page_url_full` varchar(254) NOT NULL,
  `page_title` varchar(200) NOT NULL,
  `page_text` text NOT NULL,
  `page_date_add` datetime NOT NULL,
  `page_date_edit` datetime DEFAULT NULL,
  `page_seo_keywords` varchar(250) DEFAULT NULL,
  `page_seo_description` varchar(250) DEFAULT NULL,
  `page_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `page_main` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `page_sort` int(11) NOT NULL,
  `page_auto_br` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`page_id`),
  KEY `page_pid` (`page_pid`),
  KEY `page_url_full` (`page_url_full`,`page_active`),
  KEY `page_title` (`page_title`),
  KEY `page_sort` (`page_sort`),
  KEY `page_main` (`page_main`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

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

-- --------------------------------------------------------

ALTER TABLE `prefix_topic` ADD `topic_index_ignore` TINYINT( 2 ) NULL DEFAULT '0',
ADD INDEX ( `topic_index_ignore` );

ALTER TABLE `prefix_topic_photo` ADD `date_add` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
ADD INDEX ( `date_add` );