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