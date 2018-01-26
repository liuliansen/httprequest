<?php
//+-------------------------------------------------------------
//| 获取原始响应的数据
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2017-09-14
//+-------------------------------------------------------------
namespace  httprequest\traits;

trait OrigResponse
{
    protected $origResponse = '';

    /**
     * 获取原始的响应结果
     * @return string
     */
    public function getOrigResponse()
    {
        return $this->origResponse;
    }

}
