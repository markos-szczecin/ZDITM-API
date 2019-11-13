<?php


namespace SzczecinInTouch\lib\SQLite;


use Generator;
use PDO;
use PDOStatement;

class SQLiteDB
{
    /** @var PDOStatement */
    private $statement;
    /**
     * PDO instance
     * @var PDO
     */
    private $pdo;

    /**
     * return in instance of the PDO object that connects to the SQLite database
     * @return PDO
     */
    public function connect()
    {
        if (!$this->pdo) {
            $this->pdo = new PDO("sqlite:" . SQL_LITE_DB);
        }

        return $this->pdo;
    }

    public function exec(string $command): int
    {
        $ret = $this->pdo->exec($command);

        return $ret === false ? -1 : intval($ret);
    }

    public function getPDO(): PDO
    {
        if (!$this->pdo) {
            $this->pdo = new PDO("sqlite:" . SQL_LITE_DB);
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
                $this->statement->bindValue(':' . $key, $inQuery, PDO::PARAM_STR);
            } else {
                $this->statement->bindValue(':' . $key, $arg, $types[$key]);
            }
        }

        return $this->statement->execute();
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
}
