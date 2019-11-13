<?php

namespace SzczecinInTouch\lib\Zditm;

use PhpParser\Node\Scalar\MagicConst\Line;
use SzczecinInTouch\mappers\Zditm\Calendar;
use SzczecinInTouch\mappers\Zditm\CalendarDates;
use SzczecinInTouch\mappers\Zditm\Lines;
use SzczecinInTouch\mappers\Zditm\LineTypes;
use SzczecinInTouch\mappers\Zditm\Trips;
use ZipArchive;

class ZditmDownloader
{
    private static $zditmUrl = 'https://www.zditm.szczecin.pl/rozklady/GTFS/latest/google_gtfs.zip';

    const DATA_ZIP_FILE = 'zditm.zip';
    const DATA_META_FILE = 'zditm_meta.dat';
    const DATA_UNZIP_DIR = 'unzipped/';
    const DATA_UNZIPPED_LINES_FILE = 'unzipped/routes.txt';
    const DATA_UNZIPPED_TRIPS_FILE = 'unzipped/trips.txt';
    const DATA_UNZIPPED_CALENDAR_DATES_FILE = 'unzipped/calendar_dates.txt';
    const DATA_UNZIPPED_CALENDAR_FILE = 'unzipped/calendar.txt';

    const TRAM_TYPE = 0;
    const BUS_TYPE = 3;

    public function __construct()
    {
        $this->update();
    }

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

    private function isDataFromToday(): bool
    {
        return $this->getMetaData()['last_download_time'] < strtotime('-1h' . date('Y-m-d 00:00:00'));
    }

    private function updateMetaFIle(string $hash)
    {
        file_put_contents(DATA_DIR . self::DATA_META_FILE, json_encode([
            'data_hash' => $hash,
            'last_download_time' => time()
        ]));
    }

    private function download()
    {
        $zip = (string) file_get_contents(self::$zditmUrl);
        file_put_contents(DATA_DIR . self::DATA_ZIP_FILE, $zip);
        $this->updateMetaFIle(sha1($zip));
    }

    private function unzip(): bool
    {
        $zip = new ZipArchive;
        if ($zip->open(DATA_DIR . self::DATA_ZIP_FILE) === TRUE) {
            $zip->extractTo(DATA_DIR . self::DATA_UNZIP_DIR);
            $zip->close();

            return true;
        } else {
            return false;
        }
    }

    private function updateLines()
    {
        $fp = fopen(DATA_DIR . self::DATA_UNZIPPED_LINES_FILE, 'rw');
        $content = [
            'bus' => [],
            'tram' => []
        ];
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

    private function update()
    {
        if ($this->isDataFromToday()) {
            ini_set('max_execution_time', 3600);
            $this->download();
            $this->unzip();
            $this->updateLines();
            $this->updateCalendar();
            $this->updateCalendarDates();
            $this->updateTrips();
        }
    }

    public function getLines(): array
    {
        static $linesData;

        if (!$linesData) {
            $linesData = (new Lines())->getAllNumbers();
        }

        return (array) $linesData;
    }

    public function getLinesNumbers(): array
    {
        $lines = [];

        foreach ($this->getLines() as $type => $lineData)
            foreach ($lineData as $id => $line)
                $lines[$type][$id] = $line['number'];

        return $lines;
    }
}
