<?php

class AddQueue extends DBMigration {

    function up() {
        DBManager::get()->query("CREATE TABLE `dropbox_queue` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) NOT NULL DEFAULT '',
  `filepath` varchar(1024) NOT NULL DEFAULT '',
  `dropboxpath` varchar(1024) ONT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->query('DROP TABLE IF EXISTS dropbox_queue');
    }

}
