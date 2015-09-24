<?php

require_once 'dropbox/autoload.php';

class DropboxCronjob extends CronJob {

    public static function getName() {
        return _('Dropbox');
    }

    public static function getDescription() {
        return _('Synchronisiert neue Dateien in verknüpfte Dropbox Accounts');
    }

    public static function getParameters() {
        return array(
            'verbose' => array(
                'type' => 'boolean',
                'default' => false,
                'status' => 'optional',
                'description' => dgettext('cardportal', 'Sollen Ausgaben erzeugt werden'),
            ),
        );
    }

    public function setUp() {
        
    }

    public function execute($last_result, $parameters = array()) {
        foreach (glob(__DIR__ . '/models/*') as $filename) {
            require_once $filename;
        }

        // Find all Files that have changed
        $time = time();
        $query = "SELECT dokumente.*, dropbox_sync.user_id as dropbox_user_id, dropbox_sync.secret
                  FROM dokumente
                  JOIN seminar_user USING (seminar_id)
                  JOIN dropbox_sync ON (seminar_user.user_id = dropbox_sync.user_id)
                  WHERE chdate > ?
                  GROUP BY user_id, dokument_id";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute(array(Config::get()->DROPBOX_LAST_SYNC));
        Config::get()->store('DROPBOX_LAST_SYNC', $time);
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Produce files and paths
            if (!$file || $file->id != $data['dokument_id']) {

                // Empty paths
                $dropboxpath = '';
                $folder = array();
                $newfolder = false;

                // Start building new path
                $file = StudipDocument::build($data);
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
                }
            }

            // Upload
            if ($dropboxpath) {
                DropboxQueue::create(array(
                    'user_id' => $data['dropbox_user_id'],
                    'filepath' => $filepath,
                    'dropboxpath' => $dropboxpath,
                    'date' => $file->chdate
                ));
            }
        }

        DropboxThreadstarter::start();
    }

    function tearDown() {
        
    }

}
