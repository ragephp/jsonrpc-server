<?php
namespace RageJsonRpc;

use RageJsonRpc\Exception\JsonRpcException;
use RageJsonRpc\Exception\JsonRpcInvalidMethodException;
use Symfony\Component\Stopwatch\Stopwatch;

class JsonRpcCall
{
    protected $service;

    /**
     * @param object $service
     */
    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handleHttpRequest(JsonRpcRequest $request, JsonRpcResponse $response)
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('api');
        $response->setStopwatch($stopWatch);
        try {
            $response->setRequest($request);
            $request->parseRequest();

            $method = $request->getMethod();
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
                    $callingParams[] = $request;
                } elseif ($param->getClass()->isSubclassOf('RageJsonRpc\\JsonRpcResponse')) {
                    $callingParams[] = $response;
                } else {
                    throw new JsonRpcInvalidMethodException('Method definition is incorrect');
                }
            }
            $result = call_user_func_array(array($this->service, $method), $callingParams);
            $response->setResult($result);
        } catch (JsonRpcException $e) {
            $response->setError($e->getMessage(), $e->getCode());
        }
        $stopWatch->stop('api');
        return $response->getHttpResponse();
    }
}