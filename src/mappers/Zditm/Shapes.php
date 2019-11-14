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
            $q = 'REPLACE INTO shapes (shape_id, shape_pt_lat, shape_pt_lon, shape_pt_sequence) VALUES (:shape_id, :shape_pt_lat, :shape_pt_lon, :shape_pt_sequence)';
            if (!$this->validateParams($params)) {
                throw new Exception('Wrong params: ' . print_r($params, true));
            }
            $this->query(
                $q,
                ['shape_id' => $params['shape_id'], 'shape_pt_lat' => $params['shape_pt_lat'], 'shape_pt_lon' => $params['shape_pt_lon'], 'shape_pt_sequence' => $params['shape_pt_sequence']],
                ['shape_id' => \PDO::PARAM_INT, 'shape_pt_lat' => \PDO::PARAM_STR, 'shape_pt_lon' => \PDO::PARAM_STR, 'shape_pt_sequence' => \PDO::PARAM_STR]
            );
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }

        return true;
    }
}
