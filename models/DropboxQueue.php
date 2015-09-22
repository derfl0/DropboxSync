<?php

/**
 * DropboxQueue.php
 * model class for table DropboxQueue
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */
class DropboxQueue extends SimpleORMap {

    protected static function configure($config = array()) {
        $config['db_table'] = 'dropbox_queue';
        $config['has_one']['sync'] = array(
            'class_name' => 'DropboxSync',
            'foreign_key' => 'user_id'
        );
        parent::configure($config);
    }

    public function execute() {
        $Client = new Dropbox\Client($this->sync->secret, "Studip/1.0");

        // Fetch metadata in dropbox
        $metadata = $Client->getMetadata($this->dropboxpath);

        // If file doesnt exists on dropbox or is older on dropbox
        if (!$metadata || $metadata['modified'] < $this->date) {
            $f = fopen($this->filepath, "rb");
        $result = $Client->uploadFile($this->dropboxpath, Dropbox\WriteMode::update(), $f);
            fclose($f);
        }
        $this->delete();
    }

}
