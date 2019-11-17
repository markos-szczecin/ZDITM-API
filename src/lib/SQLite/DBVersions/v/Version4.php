<?php

namespace SzczecinInTouch\lib\SQLite\DBVersions\v;

use SzczecinInTouch\lib\SQLite\DBVersions\aVersion;

class Version4 extends aVersion
{
    protected $v = 4;

    public function query()
    {
        $commands = [];
        $commands[] = 'CREATE TABLE IF NOT EXISTS stops (
                        `stop_id` INTEGER PRIMARY KEY,
                        `stop_code` INTEGER NOT NULL,
                        `stop_name` VARCHAR(100) NOT NULL DEFAULT \'\',
                        `stop_desc` VARCHAR(255) NOT NULL DEFAULT \'\',
                        `stop_lat` VARCHAR(50) NOT NULL ,
                        `stop_lon` VARCHAR(50) NOT NULL ,
                        `stop_url` VARCHAR(100) NOT NULL DEFAULT \'\',
                        `location_type` INTEGER(1) DEFAULT 0,
                        `parent_station` INTEGER NULL DEFAULT NULL,
                        `wheelchair_boarding` INTEGER (1) DEFAULT 0
                      )';

        $commands[] = 'CREATE INDEX IF NOT EXISTS calendar_dates_service_id_date ON calendar_dates(service_id, date)';
        $commands[] = 'CREATE INDEX IF NOT EXISTS shapes_shape_id ON shapes(shape_id)';
        $commands[] = 'CREATE INDEX IF NOT EXISTS calendar_service_id ON calendar(service_id)';
        $commands[] = 'CREATE INDEX IF NOT EXISTS trips_service_id ON trips(service_id)';
        foreach ($commands as $command) {
            if (false === $this->getDB()->exec($command)) {
                print_r($this->getDB()->lastErrorMsg()) . PHP_EOL;
            }
        }
    }
}
