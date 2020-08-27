<?php


namespace thinkEasy\form;

/**
 * 高德地图
 * Class Map
 * @package thinkEasy\form
 */
class Map extends Field
{
    protected $attrs = [
        'zoom',
        'center',
    ];

    public function __construct($field, $label, $arguments = [])
    {
        parent::__construct($field, $label, $arguments);
        $this->template = 'amap';
        $map = config('admin.map');
        $this->setAttr('api-key', $map[$map['default']]['api_key']);
    }
    public function render()
    {
        list($attrStr, $tableScriptVar) = $this->parseAttr();
        return "<eadmin-amap {$attrStr}></eadmin-amap>";
    }
}
