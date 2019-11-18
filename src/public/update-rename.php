<?php
/**
 * Copy data from temporary database to main database and remove temporary database
 */

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

use SzczecinInTouch\lib\Zditm\ZditmUpdater;

ZditmUpdater::get()->switchBases();
