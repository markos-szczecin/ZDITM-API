<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Trips extends Mapper
{
    private function correctParams(&$params)
    {
        $params['low_floor'] = isset($params['low_floor']) && intval($params['low_floor']) === 1 ? 1 : 0;
        $params['wheelchair_accessible'] = isset($params['wheelchair_accessible']) &&  intval($params['wheelchair_accessible']) === 1 ? 1 : 0;
    }

    public function add(array $params): bool
    {
        $this->correctParams($params);
        $q = 'INSERT INTO trips (
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
                    'trip_id' => SQLITE3_TEXT,
                    'route_id' => SQLITE3_TEXT,
                    'service_id' => SQLITE3_TEXT,
                    'direction_id' => SQLITE3_INTEGER,
                    'trip_headsign' => SQLITE3_TEXT,
                    'block_id' => SQLITE3_TEXT,
                    'shape_id' => SQLITE3_TEXT,
                    'low_floor' => SQLITE3_INTEGER,
                    'wheelchair_accessible' => SQLITE3_INTEGER
                ]
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
                    'route_id' => $row[0],
                    'service_id' => $row[1],
                    'trip_id' => $row[2],
                    'trip_headsign' => $row[3],
                    'direction_id' => $row[4],
                    'block_id' => $row[5],
                    'shape_id' => $row[6],
                    'wheelchair_accessible' => $row[7],
                    'low_floor' => $row[8]
                ]);
            }
            $this->getDb()->getPDO()->query('COMMIT');
        }
    }
}
