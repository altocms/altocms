ALTER TABLE `prefix_subscribe` ADD `user_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL AFTER `target_id` ,
ADD INDEX ( `user_id` ) ;

ALTER TABLE `prefix_content` ADD `content_cancreate` tinyint(1) NOT NULL DEFAULT '0' AFTER `content_candelete`;

ALTER TABLE `prefix_content_values` ADD `value_type` enum('number','string','text') NOT NULL DEFAULT 'string' AFTER `field_type` ,
ADD INDEX ( `value_type` ) ;

ALTER TABLE `prefix_comment` ADD `comment_user_edit` INT(11) UNSIGNED NULL DEFAULT '0' AFTER `comment_date`;
ALTER TABLE `prefix_comment` ADD `comment_date_edit` DATETIME NULL DEFAULT NULL AFTER `comment_date`;
