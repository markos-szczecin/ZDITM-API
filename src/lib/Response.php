<?php

namespace SzczecinInTouch\lib;

class Response
{
    const STATUS_OK = 0;
    const STATUS_ERROR = 1;

    private $responseStatus = self::STATUS_OK;
    private $responseMsg = '';
    private $responseData = [];

    /** @var Response */
    private static $instance;

    private function __construct() {}

    /**
     * @return Response
     */
    public static function get(): Response
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param int $status
     * @return Response
     */
    public function setStatus(int $status): Response
    {
        $this->responseStatus = $status;

        return $this;
    }

    /**
     * @param string $msg
     * @return Response
     */
    public function setMsg(string $msg): Response
    {
        $this->responseMsg = $msg;

        return $this;
    }

    /**
     * @param array $data
     * @return Response
     */
    public function setData(array $data): Response
    {
        $this->responseData = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function createResponse(): string
    {
        header('Content-type: application/json');

        return (string) json_encode([
            'status' => $this->responseStatus,
            'msg' => $this->responseMsg,
            'data' => $this->responseData
        ]);
    }
}
