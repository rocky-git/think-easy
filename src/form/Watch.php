<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-10-06
 * Time: 12:42
 */

namespace thinkEasy\form;

use ArrayAccess;
class Watch implements ArrayAccess
{
    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($field = '')
    {
        if (empty($field)) {
            return $this->data;
        } else {
            return $this->data[$field];
        }
    }

    public function set($field, $value)
    {
        $this->data[$field] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }
    // ArrayAccess
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    public function offsetExists($name): bool
    {
        return $this->__isset($name);
    }

    public function offsetUnset($name)
    {
        $this->__unset($name);
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }
    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }
    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return !is_null($this->get($name));
    }
}