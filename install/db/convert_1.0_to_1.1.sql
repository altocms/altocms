-- Уменьшение длины поля (было DATETIME)
ALTER TABLE  prefix_user CHANGE  `user_profile_birthday`  `user_profile_birthday` DATE NULL DEFAULT NULL ;

-- Таблица связей типов блогов и типов контента
CREATE TABLE prefix_blog_type_content
(
  content_id   INT UNSIGNED NOT NULL,
  blog_type_id INT UNSIGNED NOT NULL
);
CREATE UNIQUE INDEX content_type_id_blog_type_id_index ON prefix_blog_type_content (content_id, blog_type_id);
CREATE INDEX blog_type_id_index ON prefix_blog_type_content (blog_type_id);
CREATE INDEX content_type_id_index ON prefix_blog_type_content (content_id);

-- Поменялась логика, теперь тип контена блога берется из связанной
-- таблицы а не из поля. Соответственно нужнол заполнить таблицу связей
-- всеми типами контента в тех типах блогов, где был разрешен
-- любой контент. Где стоял один ниего делать не нужно, обновление
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

ALTER TABLE  `prefix_blog` ADD  `blog_order` INT NULL DEFAULT  '0',
ADD INDEX (  `blog_order` ) ;

ALTER TABLE  `prefix_topic` ADD  `topic_order` INT NULL DEFAULT  '0',
ADD INDEX (  `topic_order` ) ;

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
ALTER TABLE `prefix_content_values` CHANGE `value_type` `value_type` ENUM('string','text','number','single-image-uploader') NULL DEFAULT NULL;
ALTER TABLE `prefix_mresource` ADD  `sort` INT NULL DEFAULT '0';
ALTER TABLE prefix_page ADD page_text_source text NOT NULL;
UPDATE prefix_page SET page_text_source = page_text;

ALTER TABLE prefix_user ADD user_role INT UNSIGNED DEFAULT 1 NOT NULL;
CREATE INDEX user_role_index ON prefix_user (user_role);
UPDATE prefix_user SET user_role = 3 WHERE user_id in (SELECT user_id FROM prefix_user_administrator);
UPDATE prefix_user SET user_role = 3 WHERE user_id = 1;

UPDATE
  prefix_mresource_target
SET
  target_type = 'photoset'
WHERE
  mresource_id IN (
    SELECT id
    FROM (SELECT
            r.uuid,
            t.id,
            p.path
          FROM
            prefix_mresource r,
            prefix_mresource_target t,
            prefix_topic_photo p
          WHERE
            t.mresource_id = r.mresource_id) n
    WHERE n.path LIKE CONCAT('%', n.uuid, '%')
);

UPDATE
  prefix_mresource_target AS mt
  LEFT JOIN prefix_mresource AS m ON m.mresource_id = mt.mresource_id
  JOIN prefix_topic_photo AS tp ON tp.path = m.path_file
SET
  mt.target_type = 'photoset'
WHERE
  mt.target_type != 'photoset';
