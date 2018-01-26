<?php
//+-------------------------------------------------------------
//| 响应头信息对象
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2018-01-25
//+-------------------------------------------------------------
namespace httprequest;

class Header
{
    protected $origHeader = '';

    protected $header = [];

    public function __construct($header,$curlExecInfo)
    {
        $this->origHeader = $header;
        $this->header = $curlExecInfo;
        if($this->origHeader){
            $resHeader = [];
            $items = explode("\r\n",$this->origHeader);
            foreach($items as $item){
                $nameLen = strpos($item,':');
                if($nameLen === false) continue;
                $name = trim(substr($item,0,$nameLen));
                if(isset( $resHeader[$name])){
                    if(is_array($resHeader[$name])){
                        $resHeader[$name][] = trim(substr($item,$nameLen+1));
                    }else{
                        $resHeader[$name] = [
                            $resHeader[$name],
                            trim(substr($item,$nameLen+1)),
                        ];
                    }
                }else{
                    $resHeader[$name] = trim(substr($item,$nameLen+1));
                }
            }
            $this->header = array_merge($this->header,$resHeader);
        }
    }

    /**
     * <pre>
     * 获取指定响应头项目
     * 如果未设置$key则返回所有头信息(含curl_getinfo()的内容)
     * @param string $key
     * @return array|mixed
     */
    public function get($key = '')
    {
        if($key){
            if(isset($this->header[$key]))
                return $this->header[$key];
            return false;
        }
        return $this->header;
    }

}