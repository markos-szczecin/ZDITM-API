<?php
try {
    (new \SzczecinInTouch\lib\SQLite\DBVersions\Migrate())->migrate();
} catch (Throwable $t) {
    var_dump($t);die;
}
