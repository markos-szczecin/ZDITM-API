<?php


namespace SzczecinInTouch\Controllers;


use Psr\Container\ContainerInterface;
use SzczecinInTouch\lib\Response;

abstract class BaseController
{
    protected $error = false;
    protected $message = '';
    protected $responseData = [];

//    protected $container;
//
//    // constructor receives container instance
//    public function __construct(ContainerInterface $container) {
//        $this->container = $container;
//    }

    protected function getResponse(): string
    {
        return Response::get()
            ->setStatus((int) $this->error)
            ->setMsg($this->message)
            ->setData($this->responseData)
            ->createResponse();
    }
}
