<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-04-18
 * Time: 10:18
 */

namespace thinkEasy\grid;


use thinkEasy\View;

class Cell extends View
{
    public function __construct()
    {
        $this->template = 'cell';
    }
}
