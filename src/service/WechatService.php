<?php


namespace thinkEasy\service;



use EasyWeChat\Factory;
use think\facade\Filesystem;
use function thinkEasy\sysconf;

class WechatService
{
    /**
     * 公众号
     * @param array $options
     * @return \EasyWeChat\OfficialAccount\Application
     * @throws \think\Exception
     */
    public static function wechat($options=[])
    {
        $config = [
            'app_id' => sysconf('wechat_appid'),
            'secret' => sysconf('wechat_secret'),
            'token' => sysconf('wechat_token'),
            'aes_key' => sysconf('wechat_aes_key'),
            'cert_path' => Filesystem::disk('safe')->path(sysconf('wechat_mch_ssl_cert')),
            'key_path' => Filesystem::disk('safe')->path(sysconf('wechat_mch_ssl_key')),
            'response_type' => 'array',
        ];
        $config = array_merge($config, $options);
        return Factory::officialAccount($config);
    }

    /**
     * 微信支付
     * @param array $options
     * @return \EasyWeChat\Payment\Application
     * @throws \think\Exception
     */
    public static function payment($options=[])
    {
        $config = [
            'app_id' => sysconf('wechat_appid'),
            'mch_id' => sysconf('wechat_mch_id'),
            'key' => sysconf('wechat_mch_key'),
            'cert_path' => Filesystem::disk('safe')->path(sysconf('wechat_mch_ssl_cert')),
            'key_path' => Filesystem::disk('safe')->path(sysconf('wechat_mch_ssl_key')),
            'response_type' => 'array',
        ];
        $config = array_merge($config, $options);
        return Factory::payment($config);
    }

    /**
     * 小程序
     * @param array $options
     * @return \EasyWeChat\MiniProgram\Application
     * @throws \think\Exception
     */
    public static function miniProgram($options=[]){
        $config = [
            'app_id' => sysconf('wechat_mini_appid'),
            'secret' => sysconf('wechat_mini_secret'),
            'response_type' => 'array',
        ];
        $config = array_merge($config, $options);
        return Factory::miniProgram($config);
    }
}
