<?php

/**
 * DropboxSync.php
 * model class for table DropboxSync
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
class DropboxSync extends SimpleORMap {

    private $client;

    protected static function configure($config = array()) {
        $config['db_table'] = 'dropbox_sync';
        parent::configure($config);
    }

    /**
     * Singleton for client
     * 
     * @return Dropbox\Client
     */
    public function getClient() {
        if (!$this->secret) {
            return null;
        }
        if (!$this->client) {
            $this->client = new Dropbox\Client($this->secret, "Studip/1.0");
        }
        return $this->client;
    }

    /**
     * Full sync of a user
     */
    public function sync() {
        $stmt = DBManager::get()->prepare('SELECT seminare.* FROM seminar_user JOIN seminare USING (seminar_id) WHERE user_id = ?');
        $stmt->execute(array($this->user_id));
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach (StudipDocument::findBySeminar_id($data['Seminar_id']) as $file) {
                $this->syncFile($file);
            }
        }

        DropboxThreadstarter::start();
    }

    public function syncNewFiles() {
        
    }

    /**
     * Synchronizes a file with dropbox
     * 
     * @param type $file
     */
    private function syncFile($file) {

        // Build filepath<
        $filepath = $GLOBALS['UPLOAD_PATH'] . '/' . substr($file->dokument_id, 0, 2) . '/' . $file->dokument_id;

        // If file is found on server
        if (file_exists($filepath)) {

            // Build paths
            $folder[] = studip_utf8encode(str_replace('/', ':', $file->filename));
            if ($file->folder) {
                $folder[] = studip_utf8encode(str_replace('/', ':', $file->folder->name));
                $newfolder = DocumentFolder::find($file->folder->range_id);
            }
            while ($newfolder) {
                $folder[] = studip_utf8encode(str_replace('/', ':', $newfolder->name));
                $newfolder = DocumentFolder::find($newfolder->range_id);
            }
            $folder[] = studip_utf8encode(str_replace('/', ':', $file->course->getFullname()));
            $folder[] = studip_utf8encode(str_replace('/', ':', $file->course->start_semester->name));

            $dropboxpath = "/" . join('/', array_reverse($folder));

            DropboxQueue::create(array(
                        'user_id' => User::findCurrent()->id,
                        'filepath' => $filepath,
                        'dropboxpath' => $dropboxpath,
                        'date' => $file->chdate
            ));
        }
    }

    public function kill() {
        $this->getClient()->disableAccessToken();
        $this->delete();
    }

}
