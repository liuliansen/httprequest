<?php
//+-------------------------------------------------------------
//| 请求url对象
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2017-09-13
//+-------------------------------------------------------------
namespace httprequest;

use httprequest\exception\UrlMethodInvalidException;
use httprequest\exception\UrlParameterInvalidException;

class Url implements \JsonSerializable
{
    private $baseUrl = '';
    private $method = '';
    private $params = [];
    private $methods = ['get','post'];
    private $encoding = false;

    /**
     * Url constructor.
     * @param $url
     * @param string $method
     * @param array $params
     * @param boolean $encoding
     * @throws UrlMethodInvalidException
     * @throws UrlParameterInvalidException
     */
    public function __construct($url, $method = 'get', array $params = [],$encoding = false)
    {
        if(!$url || !is_string($url)){
            throw new UrlParameterInvalidException('url不是一个有效的地址');
        }
        $method = strtolower($method);
        if(!in_array($method,$this->methods)){
            throw new UrlMethodInvalidException('不支持的请求方式：'.$method);
        }
        $this->baseUrl  = $url;
        $this->method   = $method;
        $this->params   = $params;
        $this->encoding = $encoding;
    }

    /**
     * 获取数组的url参数格式字符串
     * @param $key
     * @param array $sub
     * @return string
     * @throws UrlParameterInvalidException
     */
    protected function getSubQueryStr($key,array $sub)
    {
        $subStr = '';
        foreach ($sub as $vv){
            if(is_array($vv)){
                throw new UrlParameterInvalidException('url请求参数不支持二维及以上数组');
            }
            if($subStr){
                $subStr .= '&'. $key .'[]='.$vv;
            }else{
                $subStr = $key .'[]='.$vv;
            }
        }
        return $subStr;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        $url = $this->baseUrl;
        if($this->method == 'get'){
            $queryStr = http_build_query($this->params);
            if($queryStr){
                $url .= (strpos($this->baseUrl,'?') === false ? '?':'&')  .$queryStr;
            }
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * 获取http请求字符串
     * @return string
     */
    public function getQueryStr()
    {
        return http_build_query($this->params);
    }

    /**
     * @return bool
     */
    public function isEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param bool $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public function jsonSerialize()
    {
        return [
            'url' => (string) $this,
            'params' => $this->params,
            'method' => $this->method
        ];
    }

}