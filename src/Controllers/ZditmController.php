<?php
namespace SzczecinInTouch\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use SzczecinInTouch\Exceptions\ZditmException;
use SzczecinInTouch\lib\Zditm\ZditmDownloader;

class ZditmController extends BaseController
{
    protected $container;
    /** @var ZditmDownloader  */
    private $zditmDownloader;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->zditmDownloader = new ZditmDownloader();
    }
    /**
     * Get available line numbers - GET
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function linesNumbers(Request $request, Response $response)
    {
        var_dump(2);;die;
        $this->responseData = $this->zditmDownloader->getLinesNumbers();

        $response->getBody()->write($this->getResponse());

        return $response;
    }

    /**
     * Get line details - GET
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @throws ZditmException
     */
    public function line(Request $request, Response $response, array $args)
    {
        /**
         * @todo
         * kierunki
         * przystanki ze współrzędnymi
         */
        if (!isset($args['number']) || !intval($args['number'])) {
            throw new ZditmException('Line Number is undefined');
        }
        $number = (int) $args['number'];

    }

    /**
     * Get line details - POST
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @throws ZditmException
     */
    public function lineWithAuth(Request $request, Response $response, array $args)
    {
       return $this->line($request, $response, $args);
    }


    /**
     * Get available line numbers - POST
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function linesNumbersWithAuth(Request $request, Response $response)
    {
        return $this->linesNumbers($request, $response);
    }
}
