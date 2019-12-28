<?php
namespace Nivekaa\Curly\Exception;

use Throwable;

/**
 * Class ClientCallException
 * @package Curly\Exception
 */
class ClientCallException extends \Exception{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
        //error message
        $errorMsg = $this->getMessage();
        if (strpos('{"error":',$errorMsg)){
            $error = json_decode($errorMsg,true);
            return $error->error->message;
        }
        return $errorMsg;
    }

    /**
     * @return int|mixed
     */
    public function getErrorCode(){
        return $this->getCode();
    }

    /**
     * @return int
     */
    public function getErrorLigne(){
        return $this->getLine();
    }
}
