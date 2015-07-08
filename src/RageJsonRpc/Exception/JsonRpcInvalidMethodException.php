<?php
namespace RageJsonRpc\Exception;

class JsonRpcInvalidMethodException extends JsonRpcException
{
    const CODE = -32602;

    public function __construct($message = "", $code = self::CODE, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}