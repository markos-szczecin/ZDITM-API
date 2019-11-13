<?php


namespace SzczecinInTouch\mappers\Zditm;


use Exception;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\mappers\Mapper;

class CalendarDates extends Mapper
{
    const EXCEPTION_SERVICE_ADDED = 1;
    const EXCEPTION_SERVICE_REMOVED = 2;

    private function checkExceptionType(int $type): bool
    {
        return $type === self::EXCEPTION_SERVICE_ADDED || $type === self::EXCEPTION_SERVICE_REMOVED;
    }

    public function add(array $params)
    {
        $q = 'REPLACE INTO calendar_dates (service_id, date, exception_type) VALUES (:service_id, :date, :exception_type)';
        try {
            if (!$this->checkExceptionType($params['exception_type'])) {
                throw new Exception('Wrong exception_type ' . $params['exception_type']);
            }
            $this->query(
                $q,
                ['service_id' => $params['service_id'], 'date' => $params['date'], 'exception_type' => $params['exception_type']],
                ['service_id' => \PDO::PARAM_STR, 'date' => \PDO::PARAM_INT, 'exception_type' => \PDO::PARAM_INT]
            );
        } catch (Exception $e) {
            Logger::errorLog($e->getMessage());

            return false;
        }
    }
}
