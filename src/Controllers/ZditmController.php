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
     * Pobranie nr linii
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function linesNumbers(Request $request, Response $response)
    {
        $this->responseData = $this->zditmDownloader->getLinesNumbers();

        $response->getBody()->write($this->getResponse());

        return $response;
    }


    /**
     * Pobranie szczegółów lini
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
     * Zgłoszenie opóźnienia
     *
     * @param Request $request
     * @param Response $response
     */
    public function delay(Request $request, Response $response)
    {

    }

    /**
     * Zgłoszenie awarii
     *
     * @param Request $request
     * @param Response $response
     */
    public function issue(Request $request, Response $response)
    {

    }

    /**
     * Przeanalizowanie przystanków w określonym promieniu i znalezienie autobusów i tramwajów w ciągu najbliższych minut
     * @param Request $request
     * @param Response $response
     */
    public function analyzeNearestStops(Request $request, Response $response)
    {

    }
}
