<?php
namespace Nivekaa\Reponse;


use Nivekaa\Curly\Exception\ClientCallException;

class ResponseBody
{
    const SUCCESS = "success";
    const ERROR = "error";
    private $data;
    private  $status;
    private $message;
    private $code;

    /**
     * ResponseBody constructor.
     * @param $data
     * @param $status
     * @param $message
     * @param $code
     */
    public function __construct($data=[], $status = null, $code=null, $message=null)
    {
        $this->data = $data;
        $this->status = $status;
        $this->message = $message;
        $this->code = $code;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }


    /**
     * @param string $result
     */
    public static function responseOk($result){
        $arr = json_decode($result, true);
        return new self($arr, self::SUCCESS, 201);
    }

    /**
     * @param \Exception $exception
     * @return ResponseBody
     */
    public static function responseErr($exception){
        return new self([], self::ERROR, 201, $exception->message());
    }

    public function toArray(){
        if ($this->getStatus() == self::ERROR)
        return [
            "code"=> $this->getCode(),
            "message" => $this->getMessage(),
            "status" => self::ERROR
        ];

        else
            return [
                "code"=> $this->getCode(),
                "data" => $this->getData(),
                "status" => self::SUCCESS
            ];
    }
}
