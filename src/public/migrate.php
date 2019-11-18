<?php

/**
 * Update database structure by analysing available Version[X].php files
 */
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\lib\SQLite\DBVersions\Migrate;

try {
    (new Migrate())->migrate();
} catch (Throwable $t) {
    Logger::errorLog($t->getMessage());
}
