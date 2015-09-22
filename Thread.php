<?php

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/bootstrap.php';
require_once 'models/DropboxQueue.php';
require_once 'models/DropboxSync.php';
require_once 'models/DropboxThreadstarter.php';
require_once 'dropbox/autoload.php';

// We need to run quite a long time
ini_set('max_execution_time', 86400);

// Prepare
$process_id = uniqid('thread');
while ($timeout < 30) {
    
    // Try to reserve a job
    DBManager::get()->execute('UPDATE dropbox_queue SET process_id = ?, startdate = ? WHERE process_id IS NULL AND (startdate IS NULL OR startdate + ? < ?) LIMIT 1', array($process_id, time(),  DropboxThreadstarter::THREAD_TIMEOUT, time()));
    
    $jobs = DropboxQueue::findByProcess_id($process_id);
    if ($jobs) {
        $job = $jobs[0];
        $job->execute();
    } else {
        $timeout++;
        sleep(1);
    }
}