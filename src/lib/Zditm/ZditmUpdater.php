<?php

namespace SzczecinInTouch\lib\Zditm;

use SzczecinInTouch\lib\SQLite\SQLiteDB;
use SzczecinInTouch\mappers\Zditm\Calendar;
use SzczecinInTouch\mappers\Zditm\CalendarDates;
use SzczecinInTouch\mappers\Zditm\Lines;
use SzczecinInTouch\mappers\Zditm\LineTypes;
use SzczecinInTouch\mappers\Zditm\Shapes;
use SzczecinInTouch\mappers\Zditm\Trips;
use ZipArchive;

/**
 * Class ZditmUpdater
 *
 * Aktualizacja rozkładu
 *
 * @package SzczecinInTouch\lib\Zditm
 */
class ZditmUpdater
{
    private static $zditmUrl = 'https://www.zditm.szczecin.pl/rozklady/GTFS/latest/google_gtfs.zip';
    private static $instance;

    const DATA_ZIP_FILE = 'zditm.zip';
    const DATA_META_FILE = 'zditm_meta.dat';
    const DATA_UNZIP_DIR = 'unzipped/';
    const DATA_UNZIPPED_LINES_FILE = 'unzipped/routes.txt';
    const DATA_UNZIPPED_TRIPS_FILE = 'unzipped/trips.txt';
    const DATA_UNZIPPED_CALENDAR_DATES_FILE = 'unzipped/calendar_dates.txt';
    const DATA_UNZIPPED_SHAPES_FILE = 'unzipped/shapes.txt';
    const DATA_UNZIPPED_CALENDAR_FILE = 'unzipped/calendar.txt';

    private function __construct()
    {
        (new \SzczecinInTouch\lib\SQLite\DBVersions\Migrate())->migrateTempBase();
    }

    public static function get(): ZditmUpdater
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Dane o ostatniej aktualizacji oraz skrót z ostatnio pobranych danych
     *
     * @return array
     */
    private function getMetaData(): array
    {
        static $data;

        if (!$data) {
            $data = json_decode((string) @file_get_contents(DATA_DIR . self::DATA_META_FILE), true);
            if (empty($data)) {
                $data = [
                    'last_download_time' => '0000-00-00',
                    'data_hash' => ''
                ];
            }
        }

        return $data;
    }

    /**
     * Czy czas ostatniego pobrania jest conajmniej ze wczoraj
     *
     * @return bool
     */
    private function isTimeToUpdate(): bool
    {
        return $this->getMetaData()['last_download_time'] < strtotime('-1 day' . date('Y-m-d 23:59:59'));
    }

    /**
     * Aktualizacja daty ostatniego pobrania oraz skrótu z danych
     *
     * @param string $hash
     */
    private function updateMetaFIle(string $hash)
    {
        file_put_contents(DATA_DIR . self::DATA_META_FILE, json_encode([
            'data_hash' => $hash,
            'last_download_time' => time()
        ]));
    }

    /**
     * Czy dostępny online rozklad jest inny od pobranego
     *
     * @param string $newHash
     *
     * @return bool
     */
    private function isNewTimetableAvailable(string $newHash): bool
    {
        return $this->getMetaData()['hash'] !== $newHash;
    }

    /**
     * @return bool - czy pobrano nową wersję rozkładu
     */
    private function download(): bool
    {
        $zip = (string) file_get_contents(self::$zditmUrl);
        $hash = sha1($zip);
        if (!$this->isNewTimetableAvailable($hash)) {
            return false;
        }
        file_put_contents(DATA_DIR . self::DATA_ZIP_FILE, $zip);
        $this->updateMetaFIle($hash);

        SQLiteDB::updateModeOn();

        return true;
    }

    /**
     * Rozpakowanie pobranych danych
     *
     * @return bool
     */
    private function unzip(): bool
    {
        $zip = new ZipArchive;
        if ($zip->open(DATA_DIR . self::DATA_ZIP_FILE) === true) {
            $zip->extractTo(DATA_DIR . self::DATA_UNZIP_DIR);
            $zip->close();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Aktualizacja linii autobusowych i tramwajowych
     */
    private function updateLines()
    {
        $fp = fopen(DATA_DIR . self::DATA_UNZIPPED_LINES_FILE, 'rw');
        fgetcsv($fp); //Pierwsza linia pomijamy
        $linesMapper = new Lines();
        while ($row = fgetcsv($fp)) {
            $linesMapper->add([
                'id' => $row[0],
                'number' => $row[1],
                'name' => $row[2],
                'type' => LineTypes::getLineTypeName(intval($row[4]))
            ]);
        }
    }

    /**
     * Aktaulizacja tras
     */
    private function updateTrips()
    {
        $fp = fopen(DATA_DIR . self::DATA_UNZIPPED_TRIPS_FILE, 'rw');
        fgetcsv($fp); //Pierwsza linia pomijamy
        $tripsMapper = new Trips();
        while ($row = fgetcsv($fp)) {
            $tripsMapper->add([
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
    }

    /**
     * Aktualizacja wyjątków w rozkładzie jazdy
     */
    private function updateCalendarDates()
    {
        $fp = fopen(DATA_DIR . self::DATA_UNZIPPED_CALENDAR_DATES_FILE, 'rw');
        fgetcsv($fp); //Pierwsza linia pomijamy
        $linesMapper = new CalendarDates();
        while ($row = fgetcsv($fp)) {
            $linesMapper->add([
                'service_id' => $row[0],
                'date' => $row[1],
                'exception_type' => (int) $row[2]
            ]);
        }
    }

    /**
     * Aktaulizacja współrzędnych geograficznych tras
     */
    private function updateShapes()
    {
        $fp = fopen(DATA_DIR . self::DATA_UNZIPPED_SHAPES_FILE, 'rw');
        fgetcsv($fp); //Pierwsza linia pomijamy
        $linesMapper = new Shapes();
        while ($row = fgetcsv($fp)) {
            $linesMapper->add([
                'shape_id' => $row[0],
                'shape_pt_lat' => $row[1],
                'shape_pt_lon' => $row[2],
                'shape_pt_sequence' => $row[3]
            ]);
        }
    }

    /**
     * Aktualizacja rozkładu jazdy
     */
    private function updateCalendar()
    {
        $fp = fopen(DATA_DIR . self::DATA_UNZIPPED_CALENDAR_FILE, 'rw');
        fgetcsv($fp); //Pierwsza lini - pomijamy
        $calendarMapper = new Calendar();
        while ($row = fgetcsv($fp)) {
            $calendarMapper->add([
                'service_id' => $row[0],
                'active_days' => [
                    $row[1] ? Calendar::MONDAY_NUM : 0,
                    $row[2] ? Calendar::TUESDAY_NUM : 0,
                    $row[3] ? Calendar::WEDNESDAY_NUM : 0,
                    $row[4] ? Calendar::THURSDAY_NUM : 0,
                    $row[5] ? Calendar::FRIDAY_NUM : 0,
                    $row[6] ? Calendar::SATURDAY_NUM : 0,
                    $row[7] ? Calendar::SUNDAY_NUM : 0
                ],
                'start_day' => strtotime($row[8]),
                'end_day' => strtotime($row[9])
            ]);
        }
    }

    /**
     * Zakończenie procesu aktualizacji - podmiana bazy danych i usunięcie tymczasowej
     */
    private function onUpdateFinish()
    {
        SQLiteDB::updateModeOff();
        if (rename(SQL_LITE_DB, 'temp.db')) {
            if (rename(SQL_LITE_DB_FOR_UPDATE, SQL_LITE_DB)) {
                unlink('temp.db');
            }
        }
    }

    public function update()
    {
        SQLiteDB::updateModeOn();
        if ($this->isTimeToUpdate()) {
            $s = microtime(true);
            ini_set('max_execution_time', 3600 * 3);
            if ($this->download()) {
                $this->unzip();
                $this->updateLines();
                $this->updateCalendar();
                $this->updateCalendarDates();
                $this->updateShapes();
                $this->updateTrips();
                $this->onUpdateFinish();
            }
            echo microtime(true) - $s; die;
        }
    }
}
