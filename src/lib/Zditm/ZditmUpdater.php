<?php

namespace SzczecinInTouch\lib\Zditm;

use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\lib\SQLite\DBVersions\Migrate;
use SzczecinInTouch\lib\SQLite\SQLiteDB;
use SzczecinInTouch\mappers\Mapper;
use SzczecinInTouch\mappers\Zditm\Calendar;
use SzczecinInTouch\mappers\Zditm\CalendarDates;
use SzczecinInTouch\mappers\Zditm\Lines;
use SzczecinInTouch\mappers\Zditm\LineTypes;
use SzczecinInTouch\mappers\Zditm\Shapes;
use SzczecinInTouch\mappers\Zditm\Stops;
use SzczecinInTouch\mappers\Zditm\StopTimes;
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
    const DATA_UNZIPPED_STOPS_FILE = 'unzipped/stops.txt';
    const DATA_UNZIPPED_STOP_TIMES_FILE = 'unzipped/stop_times.txt';
    const DATA_UNZIPPED_CALENDAR_FILE = 'unzipped/calendar.txt';

    private function __construct()
    {
    }

    public static function get(): ZditmUpdater
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get data of last download time and hash of last downloaded data
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
     * Was last download yesterday
     *
     * @return bool
     */
    private function isTimeToUpdate(): bool
    {
        return $this->getMetaData()['last_download_time'] < strtotime('-1 day' . date('Y-m-d 23:59:59'));
    }

    /**
     * Update last download time and hash of last downloaded data
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
     * Is new timetable available - comparing hashes
     *
     * @param string $newHash
     *
     * @return bool
     */
    private function isNewTimetableAvailable(string $newHash): bool
    {
        return $this->getMetaData()['data_hash'] !== $newHash;
    }

    /**
     * @return bool - Is downloaded timetable newer then current
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

        return true;
    }

    /**
     * Unzip downloaded data
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
     * Update data about available trams and buses
     */
    private function updateLines()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_LINES_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }

        (new Lines())->addFromCsv($fp);
    }

    /**
     * Updates trips
     */
    private function updateTrips()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_TRIPS_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }
        (new Trips())->addFromCsv($fp);
    }

    /**
     * Update exceptions in timetable
     */
    private function updateCalendarDates()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_CALENDAR_DATES_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }

        (new CalendarDates())->addFromCsv($fp);
    }

    /**
     * Update routes coordinates (point by point)
     */
    private function updateShapes()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_SHAPES_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }

        (new Shapes())->addFromCsv($fp);
    }

    /**
     * Open specific file
     *
     * @param string $filePath
     *
     * @return resource
     * @throws Exception
     */
    private function openFile(string $filePath)
    {
        $fp = fopen(DATA_DIR . $filePath, 'rw');
        if (is_resource($fp)) {
            fgetcsv($fp); //Pierwsza linia pomijamy
            return $fp;
        }
        throw new Exception('File ' . $filePath . ' not found');
    }

    /**
     * Update stops data (coordinates etc.)
     */
    private function updateStops()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_STOPS_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }
        (new Stops())->addFromCsv($fp);
    }

    /**
     * Update stop times data
     */
    private function updateStopTimes()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_STOP_TIMES_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }

        (new StopTimes())->addFromCsv($fp);
    }

    /**
     * Update timetable
     */
    private function updateCalendar()
    {
        try {
            $fp = $this->openFile(self::DATA_UNZIPPED_CALENDAR_FILE);
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());
            return false;
        }
        (new Calendar())->addFromCsv($fp);
    }

    /**
     * Ending update process - switch databases names and remove temp database
     */
    public function switchBases()
    {
        if (file_exists(SQL_LITE_DB_FOR_UPDATE)) {
            if (copy(SQL_LITE_DB_FOR_UPDATE, SQL_LITE_DB)) {
                @unlink(SQL_LITE_DB_FOR_UPDATE);
            }
        }
    }

    /**
     * Remove all sqlite helper base
     */
    private function eraseOldData()
    {
        (new Mapper())->eraseAll();
    }

    /**
     * Update process which includes
     * - download last available timetable
     * - compare hash of downloaded data with hash of data on our server
     * - update temporary SQLite DB if new data available
     *
     * @return bool
     */
    public function update(): bool
    {
        $error = false;
        if ($this->isTimeToUpdate()) {
            $s = microtime(true);
            ini_set('max_execution_time', 300);
            if ($this->download()) {
                try {
                    (new Migrate())->migrateTempBase();
                    $this->unzip();
                    $this->eraseOldData();
                    $this->updateLines();
                    $this->updateCalendar();
                    $this->updateCalendarDates();
                    $this->updateShapes();
                    $this->updateTrips();
                    $this->updateStops();
                    $this->updateStopTimes();
                } catch (\Throwable $t) {
                    Logger::errorLog($t->getMessage());
                    $error = true;
                }
            } else {
                $error = true;
            }
            echo microtime(true) - $s; die;
        } else {
            $error = true;
        }

        return !$error;
    }
}
