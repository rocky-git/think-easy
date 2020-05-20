<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-05-19
 * Time: 23:56
 */

namespace thinkEasy\form;


class File extends Field
{
    public function __construct($field, $label, array $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        $html = "<eadmin-upload {$attrStr}></eadmin-upload>";
        return $html;
    }
}