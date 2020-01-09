<?php
namespace Nivekaa\Curly\Exception;


use Throwable;

class ParserException extends \Exception
{
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
        return $this->getMessage();
    }
}
