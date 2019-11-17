<?php

namespace SzczecinInTouch\lib\SQLite\DBVersions\v;

use SzczecinInTouch\lib\SQLite\DBVersions\aVersion;

class Version6 extends aVersion
{
    protected $v = 6;

    public function query()
    {
        $commands = [];

        $commands[] = 'INSERT OR IGNORE INTO line_types (name) VALUES (\'tram\');';
        $commands[] = 'INSERT OR IGNORE INTO line_types (name) VALUES (\'bus\');';

        foreach ($commands as $command) {
            if (false === $this->getDB()->exec($command)) {
                print_r($this->getDB()->lastErrorMsg()) . PHP_EOL;
            }
        }
    }
}
