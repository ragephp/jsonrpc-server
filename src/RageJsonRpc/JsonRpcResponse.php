<?php
namespace RageJsonRpc;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonRpcResponse
{
    protected $request;
    private $error = null;
    private $result = false;

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
            'id' => $this->getRequest()->getId()
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
        return $response;
    }
}