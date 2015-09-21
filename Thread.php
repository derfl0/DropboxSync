<?php

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/bootstrap.php';
require_once 'models/DropboxQueue.php';
require_once 'models/DropboxSync.php';
require_once 'dropbox/autoload.php';

// Extract params
parse_str(implode('&', array_slice($argv, 1)), $_GET);

// https://www.youtube.com/watch?v=ZXsQAXx_ao0
if ($_GET['id']) {
    $job = DropboxQueue::find($_GET['id']);
    $job->execute();
}
