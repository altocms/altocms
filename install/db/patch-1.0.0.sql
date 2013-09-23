-- --------------------------------------------------------
-- Patch from 0.9.7.x upto 1.0.0

-- --------------------------------------------------------

--
-- Структура таблицы `prefix_blog_type`
--

CREATE TABLE IF NOT EXISTS `prefix_blog_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_code` varchar(10) NOT NULL,
  `allow_add` tinyint(1) DEFAULT '1',
  `min_rating` float DEFAULT '0',
  `show_title` tinyint(1) DEFAULT '1',
  `index_ignore` tinyint(1) DEFAULT '0',
  `membership` tinyint(4) DEFAULT '0',
  `acl_write` int(11) DEFAULT NULL,
  `min_rate_write` float DEFAULT '0',
  `acl_read` int(11) DEFAULT NULL,
  `min_rate_read` float DEFAULT '0',
  `acl_comment` int(11) DEFAULT NULL,
  `min_rate_comment` float DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `norder` int(11) DEFAULT '0',
  `candelete` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`type_code`),
  KEY `numord` (`norder`),
  KEY `allow_add` (`allow_add`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `prefix_blog_type`
--

INSERT INTO `prefix_blog_type` (`id`, `type_code`, `allow_add`, `min_rating`, `show_title`, `index_ignore`, `membership`, `acl_write`, `min_rate_write`, `acl_read`, `min_rate_read`, `acl_comment`, `min_rate_comment`, `active`, `norder`, `candelete`) VALUES
(1, 'personal', 0, 0, 1, 0, 0, 0, 0, 1, 0, 2, -10, 1, 0, 0),
(2, 'open', 1, 1, 1, 0, 1, 2, -10, 1, 0, 2, -10, 1, 0, 0),
(3, 'close', 1, 1, 1, 1, 2, 4, 0, 4, 0, 4, -10, 1, 0, 0),
(4, 'hidden', 0, 10, 0, 1, 4, 4, 0, 4, 0, 4, -10, 1, 0, 0);

ALTER TABLE `prefix_topic` ADD `topic_index_ignore` TINYINT( 2 ) NULL DEFAULT '0',
ADD INDEX ( `topic_index_ignore` )