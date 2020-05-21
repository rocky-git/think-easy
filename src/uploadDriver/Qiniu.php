<?php

namespace think\filesystem\driver;

use Overtrue\Flysystem\Qiniu\QiniuAdapter;
use think\Cache;
use think\filesystem\Driver;

class Qiniu extends Driver
{
    protected function createAdapter(): AdapterInterface
    {

        return new QiniuAdapter(
            $this->config['accessKey'],
            $this->config['secretKey'],
            $this->config['bucket'],
            $this->config['domain']
        );
    }
}