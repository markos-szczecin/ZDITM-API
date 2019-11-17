<?php
//TODO do crona
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

use SzczecinInTouch\lib\Zditm\ZditmUpdater;

ZditmUpdater::get()->switchBases();
