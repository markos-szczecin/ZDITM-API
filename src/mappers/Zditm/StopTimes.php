<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class StopTimes extends Mapper
{
    public function addFromCsv($fileHandler)
    {
        if (is_resource($fileHandler)) {
            $this->getDb()->getPDO()->query('BEGIN');
            while ($row = fgetcsv($fileHandler)) {
                $this->add([
                    'trip_id' => $row[0],
                    'arrival_time' => $row[1],
                    'departure_time' => $row[2],
                    'stop_id' => $row[3],
                    'stop_sequence' => $row[4],
                    'stop_headsign' => $row[5],
                    'pickup_type' => $row[6],
                    'drop_off_type' => $row[7]
                ]);
            }
            $this->getDb()->getPDO()->query('COMMIT');
        }
    }

    public function add(array $params)
    {
        try {
            $q = 'INSERT INTO stop_times (
                    trip_id,
                    arrival_time,
                    departure_time,
                    stop_id,
                    stop_sequence,
                    stop_headsign,
                    pickup_type,
                    drop_off_type) VALUES (
                    :trip_id,
                    :arrival_time,
                    :departure_time,
                    :stop_id,
                    :stop_sequence,
                    :stop_headsign,
                    :pickup_type,
                    :drop_off_type)';
            $this->query(
                $q,
                ['trip_id' => $params['trip_id'], 'arrival_time' => $params['arrival_time'], 'departure_time' => $params['departure_time'], 'stop_id' => $params['stop_id'], 'stop_sequence' => $params['stop_sequence'], 'stop_headsign' => $params['stop_headsign'],'pickup_type' => $params['pickup_type'], 'drop_off_type' => $params['drop_off_type']],
                ['trip_id' => SQLITE3_TEXT, 'arrival_time' => SQLITE3_TEXT, 'departure_time' => SQLITE3_TEXT, 'stop_id' => SQLITE3_INTEGER, 'stop_sequence' => SQLITE3_INTEGER, 'stop_headsign' => SQLITE3_TEXT,'pickup_type' => SQLITE3_INTEGER, 'drop_off_type' => SQLITE3_INTEGER]
            );
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }

        return true;
    }
}
