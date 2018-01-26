<?php
//+-------------------------------------------------------------
//| 请求调试信息
//+-------------------------------------------------------------
//| Author Liu LianSen <liansen@d3zz.com> 
//+-------------------------------------------------------------
//| Date 2018-01-25
//+-------------------------------------------------------------

namespace httprequest;

final class Debugger
{
    static public function dump()
    {
        echo "<pre>";
        foreach (func_get_args() as $arg){
            var_dump($arg);
        }
    }

    static public function createDebugTable($data)
    {
        $tableStyle = ' cellspacing="1" style="background:#000000;" ';
        $tdStyle = ' style="background:#fff;padding:5px 20px" ';
        echo '<table'. $tableStyle .'>';
        foreach ($data as $k => $item){
            echo '<tr>';
            echo '<td'.$tdStyle.'>'.$k.'</td>';
            echo '<td'.$tdStyle.'>';
            if(is_object($item)||is_array($item)){
                Debugger::dump($item);
            }else {
                if (is_string($item)) {
                    echo '"', $item, '"';
                } elseif(is_null($item)) {
                    echo 'NULL';
                }else{
                    echo $item;
                }
            }
            echo '</td></tr>';
        }
        echo '</table>';
        return true;
    }

    /**
     * 打印调试请求信息
     * @param $data
     */
    static public function debug($data)
    {
        self::createDebugTable($data);
    }

}