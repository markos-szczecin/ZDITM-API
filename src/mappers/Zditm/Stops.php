<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Stops extends Mapper
{
    public function addFromCsv($fileHandler)
    {
        if (is_resource($fileHandler)) {
            $this->getDb()->getPDO()->query('BEGIN');
            while ($row = fgetcsv($fileHandler)) {
                $this->add([
                    'stop_id' => $row[0],
                    'stop_code' => $row[1],
                    'stop_name' => $row[2],
                    'stop_desc' => $row[3],
                    'stop_lat' => $row[4],
                    'stop_lon' => $row[5],
                    'stop_url' => $row[6],
                    'location_type' => $row[7],
                    'parent_station' => $row[8],
                    'wheelchair_boarding' => $row[9],
                ]);
            }
            $this->getDb()->getPDO()->query('COMMIT');
        }
    }

    public function add(array $params)
    {
        try {
            $q = 'INSERT INTO stops (
                    stop_id,
                    stop_code,
                    stop_name,
                    stop_desc,
                    stop_lat,
                    stop_lon,
                    stop_url,
                    location_type,
                    parent_station,
                    wheelchair_boarding) VALUES (
                    :stop_id,
                    :stop_code,
                    :stop_name,
                    :stop_desc,
                    :stop_lat,
                    :stop_lon,
                    :stop_url,
                    :location_type,
                    :parent_station,
                    :wheelchair_boarding)';
            $this->query(
                $q,
                ['stop_id' => $params['stop_id'], 'stop_code' => $params['stop_code'], 'stop_name' => $params['stop_name'], 'stop_desc' => $params['stop_desc'], 'stop_lat' => $params['stop_lat'], 'stop_lon' => $params['stop_lon'],'stop_url' => $params['stop_url'], 'location_type' => $params['location_type'], 'parent_station' => $params['parent_station'], 'wheelchair_boarding' => $params['wheelchair_boarding']],
                ['stop_id' => SQLITE3_INTEGER, 'stop_code' => SQLITE3_INTEGER, 'stop_name' => SQLITE3_TEXT, 'stop_desc' => SQLITE3_TEXT, 'stop_lat' => SQLITE3_TEXT, 'stop_lon' => SQLITE3_TEXT,'stop_url' => SQLITE3_TEXT, 'location_type' => SQLITE3_INTEGER, 'parent_station' => SQLITE3_INTEGER, 'wheelchair_boarding' => SQLITE3_INTEGER]
            );
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }

        return true;
    }
}
