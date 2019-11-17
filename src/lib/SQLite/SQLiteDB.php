<?php


namespace SzczecinInTouch\lib\SQLite;


use Generator;
use PDO;
use SQLite3;
use SQLite3Stmt;

class SQLiteDB
{
    /** @var SQLite3Stmt */
    private $statement;
    /**
     * PDO instance
     * @var SQLite3
     */
    private $pdo;

    const STANDARD_MODE = 1; //normalne użytkownie aplikacji
    const UPDATE_MODE = 2; //jesteśmy w trakcie aktualizacji bazy danych

    private static $mode = self::STANDARD_MODE;

    /**
     * return in instance of the PDO object that connects to the SQLite database
     * @return SQLite3
     */
    public function connect(): SQLite3
    {
        if (!$this->pdo) {
            $this->getPDO();
        }

        return $this->pdo;
    }

    public function exec(string $command): int
    {
        $ret = $this->pdo->exec($command);

        return $ret === false ? -1 : intval($ret);
    }

    public function getPDO(): SQLite3
    {
        if (!$this->pdo) {
            if (self::$mode === self::UPDATE_MODE) {
                $this->pdo = new SQLite3( SQL_LITE_DB_FOR_UPDATE);
            }  else {
                $this->pdo = new SQLite3(SQL_LITE_DB);
            }
        }
        return $this->pdo;
    }

    /**
     * @param $query
     * @param array $args
     * @param array $types
     *
     * @return bool
     */
    public function query($query, array $args = [], array $types = []): bool
    {
        $this->statement = $this->getPDO()->prepare($query);
        foreach ($args as $key => $arg) {
            if (is_array($arg)) {
                $inQuery = implode(',', array_fill(0, count($arg), '?'));
                $this->statement->bindValue(':' . $key, $inQuery, SQLITE3_TEXT);
            } else {
                $this->statement->bindValue(':' . $key, $arg, $types[$key]);
            }
        }

        if (!$this->statement->execute()) {
            return false;
        }
        return true;
    }

    /**
     * @return Generator
     */
    public function fetchAll(): Generator
    {
        while ($row = $this->statement->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * Włączenie trybu aktualizacji - operowanie na tymczasowej bazie danych
     */
    public static function updateModeOn()
    {
        self::$mode = self::UPDATE_MODE;
    }

    /**
     * Wyłaczenie trybu aktualizacji - operowanie na standardowej bazie danych
     */
    public static function updateModeOff()
    {
        self::$mode = self::STANDARD_MODE;
    }
}
