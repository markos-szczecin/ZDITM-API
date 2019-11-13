<?php

use DI\Container;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

$container = new Container();

// Set container to create App with on AppFactory
$container->set('ZditmDownloader', function (ContainerInterface $c) {
    return '';
});

AppFactory::setContainer($container);
