<?php
//TODO do crona

@unlink('sqlite_szczecin_in_touch_temp.db');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

use SzczecinInTouch\lib\SQLite\DBVersions\Migrate;
use SzczecinInTouch\lib\Zditm\ZditmUpdater;

(new Migrate())->migrateTempBase();

ZditmUpdater::get()->update();
