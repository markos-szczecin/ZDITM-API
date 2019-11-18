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

    /**
     * Initializer constructor.
     *
     * @param App $app
     */
    private function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param App $app
     *
     * @return Initializer
     */
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
        //Without authorization
        $this->app->get('/line-numbers', ZditmController::class . ':linesNumbers');
        $this->app->get('/line/{number}', ZditmController::class . ':line');
    }

    private function initPost()
    {
        //With authorization
        $this->manageAuth();
        $this->app->get('/line-numbers', ZditmController::class . ':linesNumbersWithAuth');
        $this->app->get('/line/{number}', ZditmController::class . ':lineWithAuth');
    }


    public function run(string $httpMethod)
    {
        $this->setErrorMessagingMode();
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
