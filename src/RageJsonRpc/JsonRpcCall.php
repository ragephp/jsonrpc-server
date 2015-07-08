<?php
namespace RageJsonRpc;

use RageJsonRpc\Exception\JsonRpcException;
use RageJsonRpc\Exception\JsonRpcInvalidMethodException;
use Symfony\Component\HttpFoundation\Request;

class JsonRpcCall
{
    protected $service;
    protected $request;
    protected $response;
    protected $logger;

    /**
     * @param object $service
     * @param JsonRpcRequest|null $jsonRequest
     * @param JsonRpcResponse|null $jsonResponse
     */
    public function __construct($service, JsonRpcRequest $jsonRequest = null, JsonRpcResponse $jsonResponse = null)
    {
        $this->service = $service;
        $this->request = $jsonRequest ? $jsonRequest : new JsonRpcRequest();
        $this->response = $jsonResponse ? $jsonResponse : new JsonRpcResponse();
    }

    /**
     * @return JsonRpcRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return JsonRpcResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function handleHttpRequest(Request $request)
    {
        try {
            $this->getRequest()->setHttpRequest($request);
            $this->getResponse()->setRequest($this->getRequest());

            $method = $this->getRequest()->getMethod();
            if (!method_exists($this->service, $method)) {
                throw new JsonRpcInvalidMethodException('Requested method does not exist');
            }
            $reader = new \ReflectionMethod($this->service, $method);
            $doc = $reader->getDocComment();
            if (!strpos($doc, '{JsonRpcMethod}')) {
                throw new JsonRpcInvalidMethodException('Requested method does not exist');
            }
            $callingParams = [ ];
            $methodParams = $reader->getParameters();
            foreach ($methodParams as $param) {
                if ($param->getClass()->isSubclassOf('RageJsonRpc\\JsonRpcRequest')) {
                    $callingParams[] = $this->getRequest();
                } elseif ($param->getClass()->isSubclassOf('RageJsonRpc\\JsonRpcResponse')) {
                    $callingParams[] = $this->getResponse();
                } else {
                    throw new JsonRpcInvalidMethodException('Method definition is incorrect');
                }
            }
            $result = call_user_func_array(array($this->service, $method), $callingParams);
            $this->getResponse()->setResult($result);
        } catch (JsonRpcException $e) {
            $this->getResponse()->setError($e->getMessage(), $e->getCode());
        }
        return $this->getResponse()->getHttpResponse();
    }


}