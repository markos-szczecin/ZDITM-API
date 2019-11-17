<?php

namespace SzczecinInTouch\lib\SQLite\DBVersions\v;

use SzczecinInTouch\lib\SQLite\DBVersions\aVersion;

class Version3 extends aVersion
{
    protected $v = 3;

    public function query()
    {
        $commands = [];
        $commands[] = 'CREATE TABLE IF NOT EXISTS calendar_dates (
                        `service_id` VARCHAR (100) NOT NULL,
                        `date` INTEGER NOT NULL,
                        `exception_type` INTEGER(1)
                      )';
        $commands[] = 'CREATE TABLE IF NOT EXISTS shapes (
                        `shape_id` INTEGER NOT NULL,
                        `shape_pt_lat` REAL NOT NULL,
                        `shape_pt_lon` REAL NOT NULL,
                        `shape_pt_sequence` INTEGER NOT NULL
                      )';
        foreach ($commands as $command) {
            if (false === $this->getDB()->exec($command)) {
                print_r($this->getDB()->lastErrorMsg() . $command) . PHP_EOL;
            }
        }
    }
}
