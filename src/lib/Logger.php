<?php


namespace SzczecinInTouch\Lib;


use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;

class Logger
{
    private static $instance = null;

    private function __construct()
    {}

    private static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new \Monolog\Logger('logs', [], [new WebProcessor(), new MemoryPeakUsageProcessor()]);
        }

        return self::$instance;
    }

    private static function getDir()
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/logs/';
    }

    public static function logException(\Exception $e)
    {
        $dir = self::getDir();
        if (!file_exists($dir . 'logger_exceptions.log')) {
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
        }

        file_put_contents($dir . 'logger_exceptions.log', $e->getMessage() . PHP_EOL . $e->getTraceAsString());
    }

    public static function errorLog($message, array $context = array())
    {
        try {
            self::getInstance()->pushHandler(new StreamHandler(self::getDir() . 'errors.log', \Monolog\Logger::ERROR));
        } catch (\Exception $e) {
            self::logException($e);
        }

        return self::getInstance()->error($message, $context);
    }
}
