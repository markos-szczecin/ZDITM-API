<?php


namespace SzczecinInTouch\lib\Auth;


use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use SzczecinInTouch\Lib\Logger;
use SzczecinInTouch\lib\Response as MyResponse;
use SzczecinInTouch\lib\Text;
use Slim\Psr7\Response;

class AuthMiddleware
{
    /** @var RequestHandler */
    private $requestHandler;
    /** @var Request */
    private $request;
    /** @var Response */
    private $response;
    private $params = [];

    private function init()
    {
        $this->response = $this->requestHandler->handle($this->request);
        $this->params = $this->request->getParsedBody();
    }

    /**
     * @return string
     */
    private function getUserName(): string
    {
        return (string) $this->params['username'];
    }

    /**
     * @return string
     */
    private function getKey(): string
    {
        return (string) $this->params['key'];
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->request = $request;
        $this->requestHandler = $handler;
        $this->init();
        try {
            if (!(new AuthSimple($this->getUserName(), $this->getKey()))->auth()) {
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

        return $this->response;
    }
}
