<?php

require_once 'dropbox/autoload.php';

class DropboxCronjob extends CronJob {

    private $clients = array();

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
        $db = DBManager::get();
        $result = $db->query('SELECT dokumente.*,dropbox_sync.user_id as dropbox_user_id,dropbox_sync.secret FROM dokumente JOIN seminar_user USING (seminar_id) JOIN dropbox_sync ON (seminar_user.user_id = dropbox_sync.user_id) GROUP BY dokument_id');
        while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
            // Produce files and paths
            if (!$file || $file->id != $data['dokument_id']) {
                
                // Empty paths
                $dropboxpath = '';
                $folder = array();
                
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
                $Client = $this->getClient($data['dropbox_user_id'], $data['secret']);
                // Fetch metadata in dropbox
                $metadata = $Client->getMetadata($dropboxpath);

                // If file doesnt exists on dropbox or is older on dropbox
                if (!$metadata || $metadata['modified'] < $file->chdate) {
                    $f = fopen($filepath, "rb");
                    $Client->uploadFile($dropboxpath, Dropbox\WriteMode::update(), $f);
                    fclose($f);
                }
            }
        }
    }

    private function getClient($user_id, $secret) {
        if (!$this->clients[$user_id]) {
            $this->clients[$user_id] = new Dropbox\Client($secret, "Studip/1.0");
        }
        return $this->clients[$user_id];
    }

    function tearDown() {
        
    }

}
