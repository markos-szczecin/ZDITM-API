<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class Calendar extends Mapper
{
    const MONDAY    = 0b0000001;
    const TUESDAY   = 0b0000010;
    const WEDNESDAY = 0b0000100;
    const THURSDAY  = 0b0001000;
    const FRIDAY    = 0b0010000;
    const SATURDAY  = 0b0100000;
    const SUNDAY    = 0b1000000;

    const MONDAY_NUM    = 1;
    const TUESDAY_NUM   = 2;
    const WEDNESDAY_NUM = 3;
    const THURSDAY_NUM  = 4;
    const FRIDAY_NUM    = 5;
    const SATURDAY_NUM  = 6;
    const SUNDAY_NUM    = 7;

    private static $daysMap = [
        self::MONDAY_NUM    => self::MONDAY,
        self::TUESDAY_NUM   => self::TUESDAY,
        self::WEDNESDAY_NUM => self::WEDNESDAY,
        self::THURSDAY_NUM  => self::THURSDAY,
        self::FRIDAY_NUM    => self::FRIDAY,
        self::SATURDAY_NUM  => self::SATURDAY,
        self::SUNDAY_NUM    => self::SUNDAY
    ];

    /**
     * @param array $days
     *
     * @return int
     */
    private function convertToBitMask(array $days): int
    {
        $bitMask = 0b0;
        foreach ($days as $day) {
            if (isset(self::$daysMap[$day])) {
                $bitMask |= self::$daysMap[$day];
            }
        }

        return $bitMask;
    }

    public function checkIsDayOfWeekAvailable(int $timestamp)
    {

    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function add(array $params): bool
    {
        {
            $q = 'REPLACE INTO calendar (service_id, active_days, start_day, end_day) VALUES (:service_id, :active_days, :start_day, :end_day)';
            try {
                $this->query(
                    $q,
                    ['service_id' => $params['service_id'], 'active_days' => $this->convertToBitMask($params['active_days']), 'start_day' => $params['start_day'], 'end_day' => $params['end_day']],
                    ['service_id' => \PDO::PARAM_STR, 'active_days' => \PDO::PARAM_STR, 'start_day' => \PDO::PARAM_INT, 'end_day' => \PDO::PARAM_INT]
                );
            } catch (Exception $e) {
                Logger::errorLog($e->getMessage());

                return false;
            }

            return true;
        }
    }
}
