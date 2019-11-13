<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Lines extends Mapper
{
    /**
     * @param array $params
     *
     * @return bool
     */
    public function add(array $params): bool
    {
        $q = 'REPLACE INTO lines (id, number, name, type) VALUES (:id, :number, :name, :type)';
        try {
            $params['type'] = LineTypes::getLineTypeName((int) $params['type']);
            if (!$params['type']) {
                throw new Exception('Unsupported line type ' . $params['type']);
            }
            $this->query(
                $q,
                ['id' => $params['id'], 'number' => $params['number'], 'name' => $params['name'], 'type' => $params['type']],
                ['id' => \PDO::PARAM_STR, 'number' => \PDO::PARAM_STR, 'name' => \PDO::PARAM_STR, 'type' => \PDO::PARAM_STR]
            );
        } catch (Exception $e) {
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
