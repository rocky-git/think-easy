<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-15
 * Time: 22:12
 */

namespace thinkEasy\grid;


class Excel
{
    /**
     * 导出excel
     * @param $columnTitle 表头标题-格式['test'=>'测试']
     * @param $datas 二维数组
     * @param $fileName 导出文件名
     */
    public static function export($columnTitle,$datas,$fileName)
    {
        set_time_limit(0);
        static $nums = 0;
        ini_set('memory_limit', '128M');
        header('Content-Type: application/vnd.ms-execl');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
        $fp = fopen('php://output', 'a');
        //设置标题
        $title = array_values($columnTitle);
        $fields = array_keys($columnTitle);
        foreach ($title as $key => $item) {
            $title[$key] = mb_convert_encoding( $item,'GBK','UTF-8');
        }
        //将标题写到标准输出中
        if($nums == 0){
            fputcsv($fp, $title);
        }
        foreach ($datas as $item){
            $row = [];
            foreach ($fields as $field){
                $value =  empty($item[$field]) ? '' : $item[$field];
                $row[] = mb_convert_encoding( $value,'GBK','UTF-8');
            }
            fputcsv($fp, $row);
            $nums++;
            if($nums == 5000){
                $nums = 0;
                ob_flush();
                flush();
            }
        }
        fclose($fp);
    }
}
