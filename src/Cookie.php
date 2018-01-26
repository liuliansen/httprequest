<?php
//+-------------------------------------------------------------
//| cookie存储对象
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2018-01-25
//+-------------------------------------------------------------
namespace httprequest;

class Cookie
{
     protected $cookies = [];



    /**
     * Cookie constructor.
     * @param Header $resHeader
     */
    public function __construct($resHeader = null)
    {
        if($resHeader){
            if($resHeader instanceof Header) {
                $cookieSet = $resHeader->get('Set-Cookie');
                if(is_array($cookieSet)){
                    foreach ($cookieSet as $set){
                        $this->cookies = array_merge( $this->cookies,$this->explodeCookieStr($set));
                    }
                }else{
                    $this->cookies = array_merge( $this->cookies,$this->explodeCookieStr($cookieSet));
                }
            }else{
                $this->cookies = $this->explodeCookieStr($resHeader);
            }
        }
    }

    /**
     * 追加cookie
     * 一般是对一个网站进行连续性的请求
     * @param Header $resHeader
     */
    public function addCookie(Header $resHeader)
    {
        $cookieSet = $resHeader->get('Set-Cookie');
        if($cookieSet){
            if(is_array($cookieSet)){
                foreach ($cookieSet as $set){
                    $this->cookies = array_merge( $this->cookies,$this->explodeCookieStr($set));
                }
            }else{
                $this->cookies = array_merge( $this->cookies,$this->explodeCookieStr($cookieSet));
            }
        }
    }

    /**
     * 将响应头的set-cookie内容分割成数组形式
     * @param $ckStr
     * @return array
     */
    protected function explodeCookieStr($ckStr)
    {
        $items = explode(';',urldecode($ckStr));
        $cookies = [];
        foreach ($items as $item){
            if(!strpos($item,'=')) continue;
            list($name,$val) = explode('=',$item);
            $cookies[trim($name)] = trim($val);
        }
        return $cookies;
    }

    /**
     * 获取指定名字的cookie
     * 如果未设置$key,则返回所有cookie
     * @param string $key
     * @return array|bool|mixed
     */
    public function get($key = '')
    {
        if($key){
            if(isset($this->cookies[$key]))
                return $this->cookies[$key];
            return false;
        }
        return $this->cookies;
    }


    /**
     * 获取请求时是有的cookie字符串
     * @return string
     */
    public function getRequestCookieStr()
    {
        $str = '';
        foreach ($this->cookies as $key=>$val){
            $str .= "{$key}={$val}; ";
        }
        $str = substr($str,0,strlen($str)-2);
        return $str;
    }

}