<?php
namespace RageJsonRpc\Exception;

class JsonRpcInvalidRequestException extends JsonRpcException
{
    const CODE = -32600;

    public function __construct($message = "", $code = self::CODE, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}