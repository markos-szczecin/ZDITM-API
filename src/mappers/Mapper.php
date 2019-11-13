<?php


namespace SzczecinInTouch\mappers;


use Exception;
use SzczecinInTouch\lib\SQLite\SQLiteDB;

abstract class Mapper
{
    /** @var SQLiteDB  */
    protected static $db;

    public function __construct()
    {
        if (!self::$db) {
            self::$db = new SQLiteDB();
        }
    }

    protected function getDb(): SQLiteDB
    {
        if (!self::$db) {
            self::$db = new SQLiteDB();
        }

        return self::$db;
    }

    /**
     * @param string $query
     * @param array $args
     * @param array $types
     *
     * @throws Exception
     */
    protected final function query(string $query, array $args = [], array $types = []): void
    {
        if (!$this->getDb()->query($query, $args, $types)) {
            throw new Exception(print_r($this->getDb()->getPDO()->errorInfo(), true));
        }
    }
}
