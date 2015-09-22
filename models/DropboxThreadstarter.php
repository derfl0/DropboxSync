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
class DropboxThreadstarter {

    const MAX_THREADS = 30;

    public function start() {
        $result = DBManager::get()->query("SELECT COUNT(*) FROM dropbox_queue WHERE process_id IS NOT NULL");
        $count = $result->fetchColumn();
        for ($i = 0; $i < self::MAX_THREADS - $count; $i++) {
            exec(PHP_BINDIR . '/php ' . dirname(__DIR__) . '/Thread.php > /dev/null 2>/dev/null &');
        }
    }

}
