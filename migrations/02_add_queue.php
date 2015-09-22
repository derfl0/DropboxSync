<?php

class AddQueue extends DBMigration {

    function up() {
        DBManager::get()->query("CREATE TABLE IF NOT EXISTS `dropbox_queue` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `filepath` varchar(1024) NOT NULL DEFAULT '',
  `dropboxpath` varchar(1024) NOT NULL DEFAULT '',
  `date` int(11) DEFAULT NULL,
  `process_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_id` (`process_id`)
) ");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->query('DROP TABLE IF EXISTS dropbox_queue');
    }

}
