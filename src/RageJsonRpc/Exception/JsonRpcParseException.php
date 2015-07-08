<?php
namespace RageJsonRpc\Exception;

class JsonRpcParseException extends JsonRpcException
{
    const CODE = -32700;

    public function __construct($message = "", $code = self::CODE, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}