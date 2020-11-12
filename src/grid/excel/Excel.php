<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-06-15
 * Time: 22:12
 */

namespace thinkEasy\grid\excel;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Excel extends AbstractExporter
{
    protected $excel;

    protected $sheet;

    protected $callback = null;

    protected $mapCallback = null;

    public function __construct()
    {
        $this->excel = new Spreadsheet();
        $this->sheet = $this->excel->getActiveSheet();
    }

    private function getLetter($i)
    {

        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        if ($i > count($letter) - 1) {
            if ($i > 51) {
                $num = ceil($i / 25);
            } else {
                $num = round($i / 25);
            }
            $j = $i % 26;

            $str = $letter[$num - 1] . $letter[$j];

            return $str;
        } else {
            return $letter[$i];
        }
    }

    /**
     * @param \Closure $closure
     */
    public function callback(\Closure $closure){
        $this->callback  = $closure;
    }
    public function export()
    {
        if(is_callable($this->callback)){
            call_user_func($this->callback, $this);
        }
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $i = 0;
        $this->filterColumns();
        $row = count($this->data) + 1;
        $letter = $this->getLetter(count($this->columns) - 1);
        $this->sheet->getStyle("A1:{$letter}{$row}")->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $i = 0;
        foreach ($this->columns as $field => $vals) {
            $values = array_column($this->data, $field);
            $str = $vals;
            foreach ($values as $v) {
                if (mb_strlen($v, 'utf-8') > mb_strlen($str, 'utf-8')) {
                    $str = $v;
                }
            }
            $width = ceil(mb_strlen($str, 'utf-8') * 2);
            $this->sheet->getColumnDimension($this->getLetter($i))->setWidth($width);
            $i++;
        }

        foreach ($this->columns as $field => $val) {
            $i++;
            $this->sheet->setCellValueByColumnAndRow($i, 1, $val);
        }
        $i = 1;
        foreach ($this->data as $key => &$val) {
            if ($this->mapCallback instanceof \Closure) {
                $val = call_user_func($this->mapCallback, $val,$this->sheet);
            }
            foreach ($this->columns as $fkey => $fval) {
                $this->sheet->setCellValueByColumnAndRow($i, $key + 2, $val[$fkey]);
                $i++;
            }
            $i = 1;
        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->fileName . '.xls"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($this->excel, 'Xls');
        $writer->save('php://output');
        exit;
    }

    public function map(\Closure $closure)
    {
        $this->mapCallback = $closure;
    }
}
