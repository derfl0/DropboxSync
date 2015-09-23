<?php

class MoveKeys extends DBMigration {

    function up() {
        Config::get()->create('DROPBOX_APP_KEY', array(
            'value' => "",
            'type' => 'string',
            'range' => 'global',
            'section' => 'dropbox',
            'description' => _('Dropbox app key. (Generated on dropbox.com)')
        ));
                Config::get()->create('DROPBOX_APP_SECRET', array(
            'value' => "",
            'type' => 'string',
            'range' => 'global',
            'section' => 'dropbox',
            'description' => _('Dropbox app secret. (Generated on dropbox.com)')
        ));

        SimpleORMap::expireTableScheme();
    }

    function down() {
        Config::get()->delete('DROPBOX_APP_KEY');
        Config::get()->delete('DROPBOX_APP_SECRET');
    }

}
