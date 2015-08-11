-- --------------------------------------------------------
-- Patch from 1.0.+ up to 1.1.+

-- --------------------------------------------------------

-- STEP 1
--  Уменьшение длины поля (было DATETIME)
ALTER TABLE  prefix_user CHANGE  `user_profile_birthday`  `user_profile_birthday` DATE NULL DEFAULT NULL ;

-- STEP 2
-- Таблица связей типов блогов и типов контента
CREATE TABLE prefix_blog_type_content
(
  content_id   INT UNSIGNED NOT NULL,
  blog_type_id INT UNSIGNED NOT NULL
);
CREATE UNIQUE INDEX content_type_id_blog_type_id_index ON prefix_blog_type_content (content_id, blog_type_id);
CREATE INDEX blog_type_id_index ON prefix_blog_type_content (blog_type_id);
CREATE INDEX content_type_id_index ON prefix_blog_type_content (content_id);

-- STEP 3
-- Поменялась логика, теперь тип контена блога берется из связанной
-- таблицы а не из поля. Соответственно нужно заполнить таблицу связей
-- всеми типами контента в тех типах блогов, где был разрешен
-- любой контент. Где стоял один ничего делать не нужно, обновление
-- работает корректно
INSERT INTO prefix_blog_type_content
(content_id, blog_type_id)
  (SELECT
     ct.content_id AS content_id,
     bt.id         AS blog_type_id
   FROM
     prefix_content AS ct,
     prefix_blog_type AS bt
   WHERE
     isnull(bt.content_type) OR bt.content_type = '');

-- STEP 4
ALTER TABLE  `prefix_blog` ADD  `blog_order` INT NULL DEFAULT  '0',
ADD INDEX (  `blog_order` ) ;

-- STEP 5
ALTER TABLE  `prefix_topic` ADD  `topic_order` INT NULL DEFAULT  '0',
ADD INDEX (  `topic_order` ) ;

-- STEP 6
-- Изменение формата таблицы 'prefix_storage'
ALTER TABLE  `prefix_storage` CHANGE  `storage_val`  `storage_val` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
ALTER TABLE  `prefix_storage` ADD  `storage_ord` INT NOT NULL DEFAULT  '1';
-- , ADD INDEX (  `storage_aut` ) ;
ALTER TABLE  `prefix_storage` DROP PRIMARY KEY ;
ALTER TABLE  `prefix_storage` ADD  `storage_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE  `prefix_storage` ADD  `storage_src` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `storage_id` ,
ADD INDEX (  `storage_src` ) ;

###################################################################################################################
# МОДУЛЬ UPLOAD - УНИВЕРСАЛЬНАЯ ЗАГРУЗКА ИЗОБРАЖЕНИЙ
#
-- STEP 7
ALTER TABLE `prefix_content_values` CHANGE `value_type` `value_type` ENUM('string','text','number','single-image-uploader') NULL DEFAULT NULL;

-- STEP 8
ALTER TABLE `prefix_mresource` ADD  `sort` INT NULL DEFAULT '0';

-- STEP 9
ALTER TABLE prefix_page ADD page_text_source text NOT NULL;
UPDATE prefix_page SET page_text_source = page_text;

-- STEP 10
ALTER TABLE prefix_user ADD user_role INT UNSIGNED DEFAULT 1 NOT NULL;
CREATE INDEX user_role_index ON prefix_user (user_role);
UPDATE prefix_user SET user_role = 3 WHERE user_id IN (SELECT user_id FROM prefix_user_administrator);
UPDATE prefix_user SET user_role = 3 WHERE user_id = 1;

-- STEP 11
UPDATE prefix_mresource_target m
  inner join prefix_mresource r on m.mresource_id = r.mresource_id
  inner join prefix_topic_photo p on m.target_id = p.topic_id
set m.target_type = 'photoset'
where p.path LIKE CONCAT('%', r.uuid, '%');

-- STEP 12
UPDATE
  prefix_mresource_target AS mt
  LEFT JOIN prefix_mresource AS m ON m.mresource_id = mt.mresource_id
  JOIN prefix_topic_photo AS tp ON tp.path = m.path_file
SET
  mt.target_type = 'photoset'
WHERE
  mt.target_type != 'photoset';

-- 1.1.0b2 --
-- STEP 13
-- Приведение полей к одному типу
ALTER TABLE  `prefix_topic_read` CHANGE  `comment_count_last`  `comment_count_last` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0';

-- STEP 14
-- Обновление поля prefix_topic.topic_date_show
UPDATE prefix_topic SET topic_date_show=topic_date_add
WHERE topic_publish=1 AND topic_date_show IS NULL;

-- STEP 15
ALTER TABLE  `prefix_topic` ADD INDEX (  `topic_date_show` );

