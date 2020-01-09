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
     * @return mixed
     */
    public function code()
    {
        return $this->getCode();
    }

    /**
     * @return string
     */
    public function message() {
        $errorMsg = $this->getMessage();
        // Check the HTTP Status code
        switch ($this->code()) {
            case 200:
                $error_status = "200: Success";
                break;
            case 404:
                $error_status = "404: API or URL Not found";
                break;
            case 500:
                $error_status = "500: servers replied with an error.";
                break;
            case 502:
                $error_status = "502: servers may be down or being upgraded. Hopefully they'll be OK soon!";
                break;
            case 503:
                $error_status = "503: service unavailable. Hopefully they'll be OK soon!";
                break;
            default:
                $error_status = "Undocumented error: " . $this->code() . " : " . $errorMsg;
                break;
        }
        return $error_status;
    }
    /**
     * @return int
     */
    public function line(){
        return $this->getLine();
    }
}
