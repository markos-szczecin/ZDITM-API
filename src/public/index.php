<?php

use DI\Container;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\lib\Response as MyResponse;
use SzczecinInTouch\Controllers\ZditmController;
use SzczecinInTouch\lib\Auth;
use SzczecinInTouch\lib\AuthException;
use SzczecinInTouch\lib\Text;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../container.php';
require __DIR__ . '/../migrate.php';

$app = AppFactory::create();

if (!defined('ENV') || ENV !== 'dev') {
    $app->addErrorMiddleware(false, true, true);
    error_reporting(0);
}

$app->add(function (Request $request, RequestHandler $handler) {
    //Authorization
    $response = $handler->handle($request);
    try {
        $params = $request->getParsedBody();
        $auth = new Auth(strval($params['username']), strval($params['key']));
        if (!$auth->auth()) {
            throw new AuthException(Text::AUTH_ERROR);
        }
    } catch (AuthException $e) {;
        echo MyResponse::get()
            ->setMsg($e->getMessage())
            ->setStatus(MyResponse::STATUS_ERROR)
            ->createResponse();
        Logger::errorLog($e->getMessage());
        exit;
    }

    return $response;
});

$container = $app->getContainer();

$app->get('/line-numbers', ZditmController::class . ':linesNumbers');
$app->get('/line/{number}', ZditmController::class . ':line');

$app->post('/delay', ZditmController::class . ':delay');
$app->post('/issue', ZditmController::class . ':issue');
$app->post('/analyze-nearest-stops', ZditmController::class . ':analyzeNearestStops');

$app->run();

