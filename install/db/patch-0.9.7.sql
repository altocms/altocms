ALTER TABLE `prefix_topic` ADD `topic_url` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
ADD INDEX ( `topic_url` );

