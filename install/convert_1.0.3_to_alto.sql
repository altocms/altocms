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
