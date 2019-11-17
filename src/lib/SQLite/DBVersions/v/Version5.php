<?php

namespace SzczecinInTouch\lib\SQLite\DBVersions\v;

use SzczecinInTouch\lib\SQLite\DBVersions\aVersion;

class Version5 extends aVersion
{
    protected $v = 5;

    public function query()
    {
        $commands = [];
        $commands[] = 'CREATE TABLE IF NOT EXISTS stop_times (
                        `trip_id` VARCHAR(100) NOT NULL ,
                        `arrival_time` VARCHAR(10) NOT NULL ,
                        `departure_time` VARCHAR(100) NOT NULL ,
                        `stop_id` INTEGER NOT NULL REFERENCES stops(stop_id) ,
                        `stop_sequence` INTEGER NOT NULL ,
                        `stop_headsign` VARCHAR(100) NOT NULL DEFAULT \'\',
                        `pickup_type` INTEGER(2) NOT NULL DEFAULT 0,
                        `drop_off_type` INTEGER(2) NOT NULL DEFAULT 0
                      )';

        $commands[] = 'CREATE INDEX IF NOT EXISTS stop_times_trip_id ON stop_times(trip_id)';
        $commands[] = 'CREATE INDEX IF NOT EXISTS stop_times_stop_id ON stop_times(stop_id)';

        foreach ($commands as $command) {
            if (false === $this->getDB()->exec($command)) {
                print_r($this->getDB()->lastErrorMsg()) . PHP_EOL;
            }
        }
    }
}
