<?php
namespace RageJsonRpc;

use RageJsonRpc\Exception\JsonRpcInvalidRequestException;
use RageJsonRpc\Exception\JsonRpcParseException;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcRequest
{
    private $httpRequest;
    private $jsonRequestRaw;
    private $id;
    private $method;
    private $params;

    public function setHttpRequest(Request $request)
    {
        $this->httpRequest = $request;
        if (!$request->isMethod('POST')) {
            throw new JsonRpcParseException('Invalid method, method should be POST');
        }
        if ($request->getContentType() != 'json') {
            throw new JsonRpcParseException('Content-Type should by application/json');
        }
        $this->jsonRequestRaw = $request->getContent();
        $this->parseJsonRequest();
    }

    protected function parseJsonRequest()
    {
        $body = json_decode($this->jsonRequestRaw, true);
        if (empty($body)) {
            throw new JsonRpcParseException('Invalid request body, should be valid json');
        }
        if (empty($body['id'])) {
            throw new JsonRpcInvalidRequestException('Invalid request body, should include id');
        }
        if (empty($body['method'])) {
            throw new JsonRpcInvalidRequestException('Invalid request body, should include method');
        }
        if (!isset($body['params'])) {
            throw new JsonRpcInvalidRequestException('Invalid request body, should include params');
        }
        $this->id = $body['id'];
        $this->method = $body['method'];
        $this->params = $body['params'];
    }

    /**
     * @return Request
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($param, $def = null)
    {
        return isset($this->params[$param]) ? $this->params[$param] : $def;
    }

    /**
     * @return mixed
     */
    public function getJsonRequestRaw()
    {
        return $this->jsonRequestRaw;
    }
}