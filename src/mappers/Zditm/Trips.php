<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Trips extends Mapper
{
    private function checkParams(&$params)
    {
        $params['low_floor'] = isset($params['low_floor']) && intval($params['low_floor']) === 1 ? 1 : 0;
        $params['wheelchair_accessible'] = isset($params['wheelchair_accessible']) &&  intval($params['wheelchair_accessible']) === 1 ? 1 : 0;
    }

    public function add(array $params)
    {
        $this->checkParams($params);
        $q = 'REPLACE INTO trips (
                trip_id,
                route_id,
                service_id,
                direction_id,
                trip_headsign,
                block_id,
                shape_id,
                low_floor,
                wheelchair_accessible
            ) VALUES (
                :trip_id,
                :route_id,
                :service_id,
                :direction_id,
                :trip_headsign,
                :block_id,
                :shape_id,
                :low_floor,
                :wheelchair_accessible
            )';
        try {
            $this->query(
                $q,
                [
                    'trip_id' => $params['trip_id'],
                    'route_id' => $params['route_id'],
                    'service_id' => $params['service_id'],
                    'direction_id' => $params['direction_id'],
                    'trip_headsign' => $params['trip_headsign'],
                    'block_id' => $params['block_id'],
                    'shape_id' => $params['shape_id'],
                    'low_floor' => $params['low_floor'],
                    'wheelchair_accessible' => $params['wheelchair_accessible']
                ],
                [
                    'trip_id' => \PDO::PARAM_STR,
                    'route_id' => \PDO::PARAM_STR,
                    'service_id' => \PDO::PARAM_STR,
                    'direction_id' => \PDO::PARAM_INT,
                    'trip_headsign' => \PDO::PARAM_STR,
                    'block_id' => \PDO::PARAM_STR,
                    'shape_id' => \PDO::PARAM_STR,
                    'low_floor' => \PDO::PARAM_INT,
                    'wheelchair_accessible' => \PDO::PARAM_INT
                ]
            );
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }

        return true;
    }
}
