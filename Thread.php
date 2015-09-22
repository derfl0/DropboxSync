<?php

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/bootstrap.php';
require_once 'models/DropboxQueue.php';
require_once 'models/DropboxSync.php';
require_once 'dropbox/autoload.php';

// Prepare
$process_id = uniqid('thread');
while ($timeout < 30) {
    
    // Try to reserve a job
    DBManager::get()->execute('UPDATE dropbox_queue SET process_id = ? WHERE process_id IS NULL LIMIT 1', array($process_id));
    
    $jobs = DropboxQueue::findByProcess_id($process_id);
    if ($jobs) {
        $job = $jobs[0];
        $job->execute();
    } else {
        $timeout++;
        sleep(1);
    }
}