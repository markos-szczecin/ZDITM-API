<?php

use Slim\Factory\AppFactory;
use SzczecinInTouch\lib\Initializer;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../container.php';

Initializer::get(AppFactory::create())->run($_SERVER['REQUEST_METHOD']);

