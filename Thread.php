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
    echo "Try to assign a job\n";
    DBManager::get()->execute('UPDATE dropbox_queue SET process_id = ?, startdate = ? WHERE process_id IS NULL OR startdate + ? < ? LIMIT 1', array($process_id, time(), DropboxThreadstarter::THREAD_TIMEOUT, time()));

    echo "Check\n";
    $jobs = DropboxQueue::findByProcess_id($process_id);
    if ($jobs) {
        echo "Found job! Executing...\n";
        $job = $jobs[0];
        try {
            $job->execute();
        } catch (Exception $e) {
            echo "Failed! ".$e->getMessage();
            sleep(1);
        }
    } else {
        echo "Nothing found. Waiting...\n";
        $timeout++;
        sleep(1);
    }
}