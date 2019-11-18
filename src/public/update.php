<?php
/** Download and update temporary SQLite database Main database remain untouched */


require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

@unlink(SQL_LITE_DB_FOR_UPDATE);

use SzczecinInTouch\lib\Zditm\ZditmUpdater;

define('UPDATE_MODE', true);

if (!ZditmUpdater::get()->update()) {
    @unlink(SQL_LITE_DB_FOR_UPDATE);

    echo 'No new timetable available';
}
