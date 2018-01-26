<?php
//+-------------------------------------------------------------
//| example
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2018-01-26
//+-------------------------------------------------------------
require '../autoload.php';


use httprequest\HttpRequest;
use httprequest\Url;
use httprequest\Debugger;

///An simple GET request
$url = new Url('http://www.baidu.com');
$req = new HttpRequest($url);
$req->request(1);
Debugger::dump($req->getResponseHeader());
Debugger::dump($req->getResponseBody());


///An other POST request
$url1 = new Url('http://www.baidu.com','post',['a'=>11,'b'=>22]);
$req1 = new HttpRequest($url1);
$req1->request(1);
Debugger::dump($req1->getResponseHeader());
Debugger::dump($req1->getResponseBody());
