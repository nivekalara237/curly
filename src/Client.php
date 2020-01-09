<?php
namespace Nivekaa\Curly;

use Nivekaa\Curly\Exception\ClientCallException;
use Nivekaa\Reponse\ResponseBody;


/***
 * Class Client
 * @package Curly
 */
class Client{
    protected $curly;
    private $baseUrl;
    private $asJson=true;
    private $debuggable=true;
    private $caching;
    private $cache_properties;
    private $optionsFinal = [];
    /**
     * Client constructor.
     * @param $url
     */
    public function __construct($baseUrl="", $debuggable = false)
    {
        $this->baseUrl = $baseUrl;
        $this->debuggable = $debuggable;
        // if the cURL extension is not available, trigger an error and stop execution
        if (!extension_loaded('curl'))
            trigger_error('php_curl extension is not loaded', E_USER_ERROR);
        $this->curly = curl_init();
        $this->caching = false;
    }

    /**
     * @return bool
     */
    public function isDebuggable(): bool
    {
        return $this->debuggable;
    }

    /**
     * @param bool $debuggale
     */
    public function setDebuggable(bool $debuggable): void
    {
        $this->debuggable = $debuggable;
    }

    /**
     * @param $method
     * @param $url
     * @param array $data
     * @param bool $headers
     * @return string
     * @throws ClientCallException
     */
    public function call($method, $url, $data= array(), $headers = false)
    {
        // DEFAULT OPTIONS:
        $deft_options = [
            CURLOPT_CONNECTTIMEOUT=>10,
            CURLOPT_TIMEOUT=> 30,
            CURLOPT_SSL_VERIFYPEER=> 0,
            CURLOPT_USERAGENT=> "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0",
            // the name of the file containing the cookie data; if the name is an empty string, no cookies are
            // loaded, but cookie handling is still enabled
            CURLOPT_COOKIEFILE=> "",
            CURLOPT_AUTOREFERER=> 1,
            // follow any "Location:" header that the server sends as part of the HTTP header - note this is recursive
            // and that PHP will follow as many "Location:" headers as specified by CURLOPT_MAXREDIRS
            CURLOPT_FOLLOWLOCATION=> 1,
            // the maximum amount of HTTP redirects to follow; used together with CURLOPT_FOLLOWLOCATION
            CURLOPT_RETURNTRANSFER=> 1,
            // the maximum amount of HTTP redirects to follow; used together with CURLOPT_FOLLOWLOCATION
            CURLOPT_MAXREDIRS=> 48,
            //CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ];
        if (version_compare(PHP_VERSION, '5.5') >= 0){
            // disable usage of @ in POST arguments
            $deft_options[CURLOPT_SAFE_UPLOAD] = 48;
        }
        $this->setOption($deft_options);

        switch ($method) {
            case "POST":
                $addOpts = [
                    CURLOPT_POST=>1,
                    CURLINFO_HEADER_OUT     =>  1,
                    //CURLOPT_HEADER          =>  1,
                    CURLOPT_NOBODY          =>  0
                ];
                if ($data)
                    $addOpts[CURLOPT_POSTFIELDS] = Parser::toJson($data);
                $this->setOption($addOpts);
                break;
            case "PUT":
                $addOpts = [
                    CURLOPT_CUSTOMREQUEST   =>  "PUT",
                    CURLINFO_HEADER_OUT     =>  1,
                    CURLOPT_HEADER          =>  1,
                    CURLOPT_NOBODY          =>  0,
                    CURLOPT_POST            =>  0,
                    //CURLOPT_BINARYTRANSFER  =>  null,
                    //CURLOPT_HTTPGET         =>  null,
                    //CURLOPT_FILE            =>  null,
                ];
                if ($data)
                    $addOpts[CURLOPT_POSTFIELDS] = Parser::toJson($data);
                $this->setOption($addOpts);
                break;
            case "DELETE":
                $addOpts = [
                    CURLINFO_HEADER_OUT     =>  1,
                    CURLOPT_CUSTOMREQUEST   =>  'DELETE',
                    CURLOPT_HEADER          =>  1,
                    CURLOPT_NOBODY          =>  0,
                    CURLOPT_POST            =>  0,
                    //CURLOPT_BINARYTRANSFER  =>  null,
                    //CURLOPT_HTTPGET         =>  null,
                    //CURLOPT_FILE            =>  null,
                ];
                if ($data)
                    $addOpts[CURLOPT_POSTFIELDS] = Parser::toJson($data);
                $this->setOption($addOpts);
                break;
            case "PATCH":
                $addOpts = [
                    CURLOPT_CUSTOMREQUEST=> "PATCH",
                ];
                if ($data)
                    $addOpts[CURLOPT_POSTFIELDS] = Parser::toJson($data);
                $this->setOption($addOpts);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                $addOpts = [
                    CURLINFO_HEADER_OUT     =>  1,
                    // CURLOPT_HEADER          =>  1,
                    CURLOPT_HTTPGET         =>  1,
                    CURLOPT_NOBODY          =>  0,
                    //CURLOPT_BINARYTRANSFER  =>  null,
                    //CURLOPT_CUSTOMREQUEST   =>  null,
                    //CURLOPT_FILE            =>  null,
                    CURLOPT_POST            =>  0,
                    //CURLOPT_POSTFIELDS      =>  null,
                ];
                $this->setOption($addOpts);
        }

        // OPTIONS FASTING
        $this->optionsFinal[CURLOPT_ENCODING]="gzip,deflate";
        $this->optionsFinal[CURLOPT_IPRESOLVE]=CURL_IPRESOLVE_V4;
        $this->optionsFinal[CURLOPT_URL]=$url;
        if (version_compare(PHP_VERSION, '7.0.7') >= 0) {
            $this->optionsFinal[CURLOPT_TCP_FASTOPEN]=1;
        }
        if(!$headers){
            $this->optionsFinal[CURLOPT_HTTPHEADER]=array('Content-Type: application/json;charset=UTF-8');
        }else{
            $headers[] = "Content-Type: application/json;charset=UTF-8";
            $this->optionsFinal[CURLOPT_HTTPHEADER]=$headers;
        }

        //start trace DEBUG
        if ($this->isDebuggable()){
            ob_start();
            $out = fopen('php://output', 'w');
            $this->optionsFinal[CURLOPT_VERBOSE]=true;
            $this->optionsFinal[CURLOPT_STDERR]=$out;
        }
        //end DEBUG

        // SET ARRAY OPTIONS
        // $this->setOption($options_X);
        curl_setopt_array($this->curly, $this->optionsFinal);
        // EXECUTE:
        $result = curl_exec($this->curly);
        //start close trace DEBUG
        if ($this->isDebuggable()){
            fclose($out);
            $debug = ob_get_clean();
            print_r($debug);
        }
        //end DEBUG

        //remove option where value is null
        $this->cleanOptions();
        $httpCode = intval(curl_getinfo($this->curly, CURLINFO_HTTP_CODE));
        /*echo ("--------------------------------------------------------\n\n
        *\n\*               ". curl_getinfo($this->curly,CURLINFO_TOTAL_TIME) ."\n
        -----------------------------------------------------------------");*/
        if (!($httpCode >= 200 && $httpCode <= 299)) {
            $errors = curl_error($this->curly);
            throw new ClientCallException($errors, $httpCode);
        }
        curl_close($this->curly);
        if ($method == 'GET' && $this->caching){
            $file_cache_name = $this->get_cache_file_name($url);

            // cache de result
            file_put_contents($file_cache_name , gzcompress($result));
            // set rights on the file
            chmod($file_cache_name, intval($this->cache_properties["chmod"], 8));
        }
        return $result;
    }

    /**
     * @param $full_path
     * @return string
     */
    private function get_cache_file_name($full_path){
        $where = $this->cache_properties["path"];
        if ( ! is_dir($where)) {
            mkdir($where, $this->cache_properties["chmod"], true);
        }
        $hash = md5($full_path);
        $file = "$where/$hash.cache";
        return $file;
    }

    /**
     * @param $url
     * @param array $param
     * @param bool $headers
     * @return ResponseBody
     */
    public function get($url,$param=[], $headers=false){
        try {
            $url = Parser::parseEndpoint($url, $this->baseUrl);
        } catch (Exception\ParserException $e) {
            return ResponseBody::responseErr($e);
        }

        if ($this->caching){
            $cachetime = $this->cache_properties["lifetime"]; //one hour

            $file_cache_name = $this->get_cache_file_name($url);
            $mtime = 0;
            if (file_exists($file_cache_name)) {
                $mtime = filemtime($file_cache_name);
            }
            $filetimemod = $mtime + $cachetime;
            if ($filetimemod < time()/* OR $skip_cache*/) {
                try {
                    $res =  $this->call("GET", $url, $param, $headers);
                    return ResponseBody::responseOk($res);
                } catch (ClientCallException $e) {
                    return ResponseBody::responseErr($e);
                }
            }else{
                $data_caching = file_get_contents($file_cache_name);
                $uncompress = gzuncompress($data_caching);
                return ResponseBody::responseOk($uncompress);
            }
        }else{
            try {
                $res =  $this->call("GET", $url, $param, $headers);
                return ResponseBody::responseOk($res);
            } catch (ClientCallException $e) {
                return ResponseBody::responseErr($e);
            }
        }
    }

    private function cleanOptions(){
        foreach ($this->optionsFinal as $key => $value) {
            if (is_null($value))
                unset($this->optionsFinal[$key]);
        }
    }

    /**
     * @param $url
     * @param $data
     * @param bool $headers
     * @return ResponseBody
     */
    public function post($url,$data,$headers=false){
        try {
            $url = Parser::parseEndpoint($url, $this->baseUrl);
        } catch (Exception\ParserException $e) {
            return ResponseBody::responseErr($e);
        }
        try {
            $res =  $this->call("POST", $url, $data, $headers);
            return ResponseBody::responseOk($res);
        } catch (ClientCallException $e) {
            return ResponseBody::responseErr($e);
        }
    }

    /**
     * @param int $timeout
     * @return Client
     */
    public function setTimeout($timeout = 3600){
        $this->optionsFinal[CURLOPT_TIMEOUT] = $timeout;
        return $this;
    }

    /**
     * @param integer $timeout
     * @return Client
     */
    public function setConnectionTimeout($timeout = 30){
        $this->optionsFinal[CURLOPT_CONNECTTIMEOUT] = $timeout;
        return $this;
    }

    public function __destruct()
    {
        if(gettype($this->curly) == 'resource')
            curl_close($this->curly);
    }

    /**
     * @param bool $caching
     * @param bool $path
     * @param int $lifetime
     * @param bool $compress
     * @param int $chmod
     */
    public function caching($caching=true,$path=false, $lifetime = 3600, $compress = true, $chmod = 0755){
        $path = $path==false && !is_string($path) && $path!==""?"curlycache":$path;
        if ($caching) {
            // save cache-related properties
            $this->cache_properties = array(
                'path'      =>  $path,
                'lifetime'  =>  $lifetime,
                'chmod'     =>  $chmod,
                'compress'  =>  $compress,
            );
            $this->caching = true;
        }
        else
            $this->caching = false;
        return $this;
    }
    /**
     * @param mixed $opts
     * @param bool $value
     * @return Client
     */
    public function setOption($opts,$value=false){
        if (isset($this->curly) && $this->curly!==null){
            if (!is_array($opts)){
                $this->optionsFinal[$opts] = $value;
            }
            if (is_array($opts)){
                foreach ($opts as $opt => $val){
                    if (is_null($val) && array_key_exists($opt, $this->optionsFinal))
                        unset($this->optionsFinal[$opt]);
                    $this->optionsFinal[$opt] = $val;
                }
            }
            elseif (is_null($value) && array_key_exists($opts, $this->optionsFinal))
                unset($this->optionsFinal[$opts]);
            else
                $this->optionsFinal[$opts] = $value;
        }
        return $this;
    }
}
