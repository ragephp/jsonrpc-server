<?php
namespace RageJsonRpc;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Stopwatch\Stopwatch;

class JsonRpcResponse
{
    protected $stopwatch;
    protected $request;
    protected $error = null;
    protected $result = false;

    public function setRequest(JsonRpcRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return JsonRpcRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function setStopwatch(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @return Stopwatch
     */
    public function getStopwatch()
    {
        return $this->stopwatch;
    }

    public function getDuration()
    {
        return $this->getStopwatch()->getEvent('api')->getDuration();
    }

    public function setError($message, $code = 0)
    {
        $this->error = [
            'code' => $code,
            'message' => $message
        ];
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    protected function getResponseArray()
    {
        $result = [
            'jsonrpc' => '2.0',
            'id' => $this->getRequest() ? $this->getRequest()->getId() : null
        ];
        if (!empty($this->error)) {
            $result['error'] = $this->error;
        } else {
            $result['result'] = $this->result;
        }
        return $result;
    }

    public function getHttpResponse()
    {
        $result = $this->getResponseArray();
        $response = new JsonResponse($result);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_UNESCAPED_UNICODE);
        $response->headers->add([ 'X-Api-Time' => $this->getDuration() ]);
        return $response;
    }
}