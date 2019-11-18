<?php


namespace SzczecinInTouch\lib;


use Slim\App;
use SzczecinInTouch\Controllers\ZditmController;
use SzczecinInTouch\lib\Auth\AuthMiddleware;

class Initializer
{
    /** @var App  */
    private $app;
    /** @var Initializer */
    private static $instance;

    private function __construct(App $app)
    {
        $this->app = $app;
    }

    public static function get(App $app): Initializer
    {
        if (!self::$instance) {
            self::$instance = new self($app);
        }

        return self::$instance;
    }

    private function setErrorMessagingMode()
    {
        if (!defined('ENV') || ENV !== 'dev') {
            $this->app->addErrorMiddleware(false, true, true);
            error_reporting(0);
        }
    }

    private function manageAuth()
    {
        $this->app->add(new AuthMiddleware());
    }

    private function initGet()
    {
        $this->app->get('/line-numbers', ZditmController::class . ':linesNumbers');
        $this->app->get('/line/{number}', ZditmController::class . ':line');
    }

    private function initPost()
    {
        $this->app->post('/delay', ZditmController::class . ':delay');
        $this->app->post('/issue', ZditmController::class . ':issue');
        $this->app->post('/analyze-nearest-stops', ZditmController::class . ':analyzeNearestStops');
    }


    public function run(string $httpMethod)
    {
        $this->setErrorMessagingMode();
        $this->manageAuth();
        switch (strtoupper($httpMethod)) {
            case 'GET':
                $this->initGet();
                break;
            case 'POST':
                $this->initPost();
                break;
        }

        $this->app->run();
    }
}
