<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Shapes extends Mapper
{
    private function validateParams($params): bool
    {
        return is_numeric($params['shape_id']) && is_numeric($params['shape_pt_lat'])  && is_numeric($params['shape_pt_lon'])  && is_numeric($params['shape_pt_sequence']);
    }

    public function add(array $params)
    {
        try {
            $q = 'INSERT INTO shapes (shape_id, shape_pt_lat, shape_pt_lon, shape_pt_sequence) VALUES (:shape_id, :shape_pt_lat, :shape_pt_lon, :shape_pt_sequence)';
            if (!$this->validateParams($params)) {
                throw new Exception('Wrong params: ' . print_r($params, true));
            }
            $this->query(
                $q,
                ['shape_id' => $params['shape_id'], 'shape_pt_lat' => $params['shape_pt_lat'], 'shape_pt_lon' => $params['shape_pt_lon'], 'shape_pt_sequence' => $params['shape_pt_sequence']],
                ['shape_id' => SQLITE3_INTEGER, 'shape_pt_lat' => SQLITE3_TEXT, 'shape_pt_lon' => SQLITE3_TEXT, 'shape_pt_sequence' => SQLITE3_TEXT]
            );
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }

        return true;
    }

    public function addFromCsv($fileHandler)
    {
        if (is_resource($fileHandler)) {
            $this->getDb()->getPDO()->query('BEGIN');
            while ($row = fgetcsv($fileHandler)) {
                $this->add([
                    'shape_id' => $row[0],
                    'shape_pt_lat' => $row[1],
                    'shape_pt_lon' => $row[2],
                    'shape_pt_sequence' => $row[3]
                ]);
            }
            $this->getDb()->getPDO()->query('COMMIT');
        }
    }
}
