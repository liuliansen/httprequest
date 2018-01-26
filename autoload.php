<?php
//+-------------------------------------------------------------
//| 
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2018-01-26
//+-------------------------------------------------------------

spl_autoload_register(function($clsName){
    $path = str_replace(['\\','httprequest'], [DIRECTORY_SEPARATOR,''],$clsName);
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src'.DIRECTORY_SEPARATOR.$path.'.php';
    if(file_exists($file))
        require $file;
});