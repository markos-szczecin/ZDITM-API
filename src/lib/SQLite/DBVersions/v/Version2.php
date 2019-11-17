<?php

namespace SzczecinInTouch\lib\SQLite\DBVersions\v;

use SzczecinInTouch\lib\SQLite\DBVersions\aVersion;

class Version2 extends aVersion
{
    protected $v = 2;

    public function query()
    {
        $commands = [];
        $commands[] = 'CREATE TABLE IF NOT EXISTS line_types (
                        `name` VARCHAR(10) PRIMARY KEY
                        )';

        $commands[] = 'CREATE TABLE IF NOT EXISTS lines (
                        `id` VARCHAR (6) PRIMARY KEY,
                        `number` VARCHAR(5) NOT NULL,
                        `name` VARCHAR(100) NOT NULL,
                        `type` VARCHAR (10) NOT NULL REFERENCES line_types(`name`)
                      )';

        $commands[] = 'CREATE TABLE IF NOT EXISTS calendar (
                    `service_id` VARCHAR(100) PRIMARY KEY ,
                    `active_days`  VARCHAR (8) NOT NULL DEFAULT "0000000",
                    `start_day`  INTEGER NOT NULL DEFAULT 0,
                    `end_day` INTEGER NOT NULL DEFAULT 0
                    )';

        $commands[] = 'CREATE TABLE IF NOT EXISTS trips (
                        `trip_id` VARCHAR(50) NOT NULL ,
                        `route_id` VARCHAR(5) NOT NULL,
                        `service_id` VARCHAR(100) NOT NULL,
                        `trip_headsign` VARCHAR(50) NOT NULL,
                        `direction_id` INTEGER NOT NULL,
                        `block_id` VARCHAR(10) NOT NULL,
                        `shape_id` VARCHAR(10) NOT NULL,
                        `wheelchair_accessible` INTEGER(1) NOT NULL DEFAULT 0,
                        `low_floor` INTEGER(1) NOT NULL DEFAULT 0,
                        FOREIGN KEY (`service_id`) REFERENCES calendar(`service_id`) ON UPDATE CASCADE ON DELETE CASCADE,
                        FOREIGN KEY (`route_id`) REFERENCES lines(`number`) ON UPDATE CASCADE ON DELETE CASCADE 
                        )';
        foreach ($commands as $command) {
            if (false === $this->getDB()->exec($command)) {
                print_r($this->getDB()->lastErrorMsg() . PHP_EOL . $command) . PHP_EOL;
            }
        }
    }
}
