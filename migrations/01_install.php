<?php

class Install extends DBMigration {

    function up() {
        DBManager::get()->query("CREATE TABLE IF NOT EXISTS dropbox_sync (user_id varchar(32) NOT NULL DEFAULT '',
  `secret` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
)");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->query('DROP TABLE IF EXISTS dropbox_sync');
    }

}
