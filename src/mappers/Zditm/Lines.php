<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Lines extends Mapper
{
    public function addFromCsv($fileHandler)
    {
        if (is_resource($fileHandler)) {
            $this->getDb()->getPDO()->query('BEGIN');
            while ($row = fgetcsv($fileHandler)) {
                $this->add([
                    'id' => $row[0],
                    'number' => $row[1],
                    'name' => $row[2],
                    'type' => LineTypes::getLineTypeName(intval($row[4]))
                ]);
            }
            $this->getDb()->getPDO()->query('COMMIT');
        }
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function add(array $params): bool
    {
        $q = 'INSERT INTO lines (id, number, name, type) VALUES (:id, :number, :name, :type)';
        try {
            $params['type'] = LineTypes::getLineTypeName((int) $params['type']);
            if (!$params['type']) {
                throw new Exception('Unsupported line type ' . $params['type']);
            }

            $this->query(
                $q,
                ['id' => $params['id'], 'number' => $params['number'], 'name' => $params['name'], 'type' => $params['type']],
                ['id' => SQLITE3_TEXT, 'number' => SQLITE3_TEXT, 'name' => SQLITE3_TEXT, 'type' => SQLITE3_TEXT]
            );
        } catch (\Throwable $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Zwraca wszystkie nr lini pogrupowane po typie
     *
     * @return array
     */
    public function getAllNumbers(): array
    {
        $this->getDb()->query('SELECT id, number, type FROM lines');
        $data = [];
        foreach ($this->getDb()->fetchAll() as $row) {
            $data[$row['type']][] = $row;
        }

        return $data;
    }
}
