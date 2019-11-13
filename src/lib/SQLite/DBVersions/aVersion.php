<?php

namespace SzczecinInTouch\lib\SQLite\DBVersions;

use PDO;
use SzczecinInTouch\lib\SQLite\SQLiteDB;

abstract class aVersion
{
    /** @var null|PDO */
    protected static $db;
    /** @var int  */
    protected $v = 1;

    abstract public function query();

    /**
     * @return PDO
     */
    protected function getDB(): PDO
    {
        if (!self::$db) {
            self::$db = new SQLiteDB();
        }

        return self::$db->connect();
    }

    public function getVersion(): int
    {
        return $this->v;
    }
}
