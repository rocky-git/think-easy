<?php

namespace think\filesystem\driver;

use think\Cache;
use think\filesystem\Driver;

class Qiniu extends Driver
{
    public function __construct(Cache $cache, array $config)
    {
        parent::__construct($cache, $config);
    }
}