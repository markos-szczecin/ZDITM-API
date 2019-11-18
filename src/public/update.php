<?php
/** Download and update temporary SQLite database Main database remain untouched */

@unlink('sqlite_szczecin_in_touch_temp.db');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

use SzczecinInTouch\lib\SQLite\DBVersions\Migrate;
use SzczecinInTouch\lib\Zditm\ZditmUpdater;

define('UPDATE_MODE', true);

(new Migrate())->migrateTempBase();

ZditmUpdater::get()->update();
