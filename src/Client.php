<?php
namespace Nivekaa\Curly;

use Nivekaa\Curly\Exception\ClientCallException;

/***
 * Class Client
 * @package Curly
 */
class Client{
    private $url;
    private $baseUrl;
    private $asJson=true;
    private $debuggale=true;
    /**
     * Client constructor.
     * @param $url
     */
    public function __construct($baseUrl="", $debuggale = false)
    {
        $this->baseUrl = $baseUrl;
        $this->debuggale = $debuggale;
    }

    /**
     * @return bool
     */
    public function isDebuggale(): bool
    {
        return $this->debuggale;
    }

    /**
     * @param bool $debuggale
     */
    public function setDebuggale(bool $debuggale): void
    {
        $this->debuggale = $debuggale;
    }

    /**
     * @param bool $var
     * @return bool
     */
    public function asJson($var=true) {
        $this->asJson = $var;
        return $this->asJson;
    }

    /**
     * @param $method
     * @param $url
     * @param array $data
     * @param bool $headers
     * @return mixed
     * @throws ClientCallException
     */
    public function call($method, $url, $data=[], $headers = false)
    {
        $url = $this->baseUrl.$url;
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        if(!$headers){
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json;charset=UTF-8',
            ));
        }else{
            $headers[] = "Content-Type: application/json;charset=UTF-8";
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        //start trace DEBUG
        if ($this->isDebuggale()){
            ob_start();
            $out = fopen('php://output', 'w');
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_STDERR, $out);
        }
        //end DEBUG

        // EXECUTE:
        $result = curl_exec($curl);

        //start trace DEBUG
        if ($this->isDebuggale()){
            fclose($out);
            $debug = ob_get_clean();
            print_r($debug);
        }
        //end DEBUG

        /**
         *  /* Check for 404 (file not found).
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // Check the HTTP Status code
        switch ($httpCode) {
        case 200:
        $error_status = "200: Success";
        return ($data);
        break;
        case 404:
        $error_status = "404: API Not found";
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
        $error_status = "Undocumented error: " . $httpCode . " : " . curl_error($curl);
        break;
        }
         */
        if (!$result) {
            $errors = curl_error($curl);
            throw new ClientCallException($errors);
        }
        curl_close($curl);
        return json_decode($result,!$this->asJson);
    }

    /**
     * @param $url
     * @param array $param
     * @param bool $headers
     * @return mixed
     * @throws ClientCallException
     */
    public function get($url,$param=[],$headers=false){
        try {
            return $this->call("GET", $url, $param, $headers);
        } catch (ClientCallException $e) {
            throw new ClientCallException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @param $url
     * @param $data
     * @param bool $headers
     * @return mixed
     * @throws ClientCallException
     */
    public function post($url,$data,$headers=false){
        try {
            return $this->call("POST", $url, $data, $headers);
        } catch (ClientCallException $e) {
            throw new ClientCallException($e->getMessage(),$e->getCode());
        }
    }
}
