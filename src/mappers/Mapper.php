<?php


namespace SzczecinInTouch\mappers;


use Exception;
use SzczecinInTouch\lib\SQLite\SQLiteDB;

class Mapper
{
    /** @var SQLiteDB  */
    protected static $db;

    public function __construct()
    {
        if (!self::$db) {
            self::$db = new SQLiteDB();
        }
    }

    public function getDb(): SQLiteDB
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
            throw new Exception(print_r($this->getDb()->getPDO()->lastErrorMsg() . PHP_EOL . $query, true));
        }
    }

    public function eraseAll()
    {
        $this->getDb()->query('DELETE FROM calendar WHERE 1;');
        $this->getDb()->query('DELETE FROM calendar_dates WHERE 1;');
        $this->getDb()->query('DELETE FROM line_types WHERE 1;');
        $this->getDb()->query('DELETE FROM lines WHERE 1;');
        $this->getDb()->query('DELETE FROM shapes WHERE 1;');
        $this->getDb()->query('DELETE FROM stop_times WHERE 1;');
        $this->getDb()->query('DELETE FROM stops WHERE 1;');
        $this->getDb()->query('DELETE FROM trips WHERE 1;');
    }
}
