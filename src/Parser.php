<?php
namespace Nivekaa\Curly;

use Nivekaa\Curly\Exception\ParserException;

class Parser
{
    public static function toJson($data){
        if ($data){
            if (is_array($data)){
                $json = json_encode($data);
                return $json;
            }elseif(is_string($data)){
                $json = json_encode($data);
                return $json;
            }
        }
        return json_encode([]);
    }

    /**
     * @param $url
     * @param string $baseUrl
     * @return string
     * @throws ParserException
     */
    public static function parseEndpoint($url, $baseUrl=''){
        $formatedUrl = "";
        if ($baseUrl && $baseUrl!=""){
            $url = trim($url,"/");
            $baseUrl = rtrim($baseUrl,"/");
            $formatedUrl = "$baseUrl/$url";
        }else{
            $url = trim($url,"/");
            $formatedUrl = $url;
        }

        // validate url

        if (filter_var($formatedUrl, FILTER_VALIDATE_URL) === FALSE){
            throw new ParserException("Not a valid URL", 500);
        }else{
            return $formatedUrl;
        }
    }
}
