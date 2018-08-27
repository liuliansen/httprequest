<?php
//+-------------------------------------------------------------
//| http请求对象，基于curl库的
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2017-09-13
//+-------------------------------------------------------------
namespace httprequest;

use httprequest\exception\RequestFailedException;
use httprequest\traits\OrigResponse;

/**
 *  http请求对象类
 * Class Request
 * @package utils\curl
 */
class HttpRequest
{
    use OrigResponse;

    /**
     * @var Url
     */
    protected $url;
    protected $callback;

    /**
     * 超时秒数
     * @var int
     */
    protected $timeOut = 30;

    /**
     * curl_getinfo执行信息
     * @var array
     */
    protected $execInfo;


    /**
     * 响应头
     * @var Header
     */
    protected $responseHeader;

    /**
     * 相应正文
     * @var string
     */
    protected $responseBody;

    protected $ch = null;

    /**
     * curl请求发生错误时代码
     * @var int
     */
    protected $errno = 0;

    /**
     * curl发生请求错误时的信息
     * @var string
     */
    protected $error = '';

    protected $requestInfo = [];

    protected $postData = null;

    /**
     * @return int
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }



    /**
     * @var Cookie
     */
    protected $cookie = null;

    /**
     * Request constructor.
     * @param Url $url
     * @param callable $callback
     */
    public function __construct(Url $url,$callback=null)
    {
        $this->url = $url;
        if($callback && is_callable($callback)){
            $this->callback = $callback;
        }
        $this->initCurl();
    }

    /**
     * 设置url对象
     * @param Url $url
     */
    public function setUrl(Url $url)
    {
        $this->url = $url;
        $this->initCurl();
    }

    /**
     * 设置请求使用的cookie容器
     * @param Cookie $cookie
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * 获取cookie容器
     * @return Cookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * 重置对象
     */
    public function reset()
    {
        @curl_close($this->ch);
        $this->ch = curl_init();
        $this->url = null;
        $this->timeOut = 30;
        $this->execInfo = [];
        $this->responseHeader = '';
        $this->responseBody   = '';
        $this->callback = null;
        $this->initCurl();
    }

    /**
     * 获取当前使用的curl对象
     * @return resource
     */
    public function getCh()
    {
        return $this->ch;
    }


    /**
     * 设置是否需要响应头
     * @param bool $needHeader
     */
    public function setChHeader($needHeader = false)
    {
        curl_setopt ($this->ch, CURLOPT_HEADER, !!$needHeader);
    }

    /**
     * 初始curl对象设置
     */
    protected function initCurl()
    {
        $this->requestInfo['url'] = $this->url;
        if($this->ch) {
            @curl_close($this->ch);
        }
        $this->ch = curl_init();
        curl_setopt ( $this->ch, CURLOPT_URL, $this->url );
        curl_setopt ( $this->ch, CURLOPT_HEADER, true);
        curl_setopt ( $this->ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $this->ch, CURLOPT_CONNECTTIMEOUT, $this->timeOut );
        curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt ( $this->ch, CURLOPT_SSL_VERIFYPEER, true );

        switch ($this->url->getMethod()){
            case 'get':
                curl_setopt ( $this->ch, CURLOPT_POST, 0 );
                break;
            case 'post':
                $this->postData =  $this->url->isEncoding()
                    ? $this->url->getQueryStr()
                    : $this->url->getParams();
                $this->requestInfo['params'] = $this->postData;
                curl_setopt ( $this->ch, CURLOPT_POST, 1 );
                curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $this->postData);
                break;
        }
    }


    /**
     * 执行请求, 如果curl_exec返回false,将跑出失败异常
     * @param  bool $debug 使用了回调时无效
     * @return bool
     * @throws RequestFailedException
     */
    public function request($debug = false)
    {
        if($this->cookie && ($_cookie = $this->cookie->getRequestCookieStr())){
            curl_setopt ( $this->ch, CURLOPT_COOKIE, $_cookie );
            $this->requestInfo['cookie'] = $_cookie;
        }
        if($this->callback){
            $result = call_user_func_array($this->callback,[$this->ch]);
            @curl_close($this->ch);
            return $result;
        }else{
            $this->requestInfo['response'] = $response = curl_exec($this->ch);
            $this->origResponse = $response;
            if($response === false){
                $this->errno = curl_errno($this->ch);
                $this->error = curl_error($this->ch);
                $this->requestInfo['curl_info'] = curl_getinfo($this->ch);
                $debug && Debugger::debug($this->requestInfo);
                curl_close($this->ch);
                throw new RequestFailedException($this->error,$this->errno);
            }else{
                list($header,$this->responseBody) = static::explodeResponse($response);
                $this->requestInfo['curl_info'] = $this->execInfo = curl_getinfo($this->ch);
            }
            $debug && Debugger::debug($this->requestInfo);

            //记录头信息
            $this->responseHeader = new Header($header,$this->execInfo);
            ///记录cookie信息
            if($this->cookie){
                $this->cookie->addCookie($this->responseHeader);
            }else{
                $this->cookie = new Cookie($this->responseHeader);
            }

            if(preg_match('/30\d/',$this->responseHeader->get('http_code'))){
                @curl_close($this->ch);
                $this->url = new Url($this->responseHeader->get('Location'));
                $this->requestInfo = [];
                $this->initCurl();
                $this->request($debug);
            }
        }

        return true;
    }


    /**
     * 切分响应数据
     * @param $responseCnt
     * @return array 成功[header,body],失败:[false,false]
     */
    static public function explodeResponse($responseCnt)
    {
        $rows = mb_split("\r\n",$responseCnt);
        //第一行不是http头协议信息，则说明没有响应头
        if( !preg_match('/HTTP\/\d\.\d\s+\d{3}/i',$rows[0])){
            return ['', $responseCnt];
        }
        //发生了10x状态
        if( preg_match('/HTTP\/\d\.\d\s+(100|101|102)/i',$rows[0])){
            $rows = mb_split("\r\n\r\n",$responseCnt);
            $body = [];
            for ($i = 2;$i<count($rows); $i++)
                $body[] = $rows[$i];
            return [$rows[1], implode("\r\n\r\n",$body)];
        }
        $splitIndex  = mb_strpos($responseCnt,"\r\n\r\n",0,'utf-8');
        $header = trim(mb_substr($responseCnt,0,$splitIndex,'utf-8'));
        $body   = trim(mb_substr($responseCnt,$splitIndex+1,null,'utf-8'));
        return [$header,$body];
    }


    /**
     * <pre>
     * 获取指定头字段
     * 使用本方法需要curl_setopt ( $ch, CURLOPT_HEADER, true);
     * 当未指定key时返回整个header头(如果有)
     * 当指定了key但是未找到时，返回false
     * @param string $key
     * @return Header
     */
    public function getResponseHeader($key = '')
    {
        return $this->responseHeader;
    }


    /**
     * @return mixed
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }


    /**
     * @return mixed
     */
    public function getExecInfo()
    {
        return $this->execInfo;
    }

    /**
     * @param int $timeOut
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
    }

    /**
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @return null
     */
    public function getPostData()
    {
        return $this->postData;
    }

}
