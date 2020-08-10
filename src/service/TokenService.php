<?php
/**
 * @Author: rocky
 * @Copyright: 广州拓冠科技 <http://my8m.com>
 * Date: 2019/7/11
 * Time: 15:15
 */

namespace thinkEasy\service;

use think\facade\Cache;
use think\facade\Request;
use thinkEasy\ApiJson;
use thinkEasy\model\AdminModel;
use thinkEasy\Service;


class TokenService extends Service
{

    use ApiJson;
    const IV = 'yHXo48tHnXWSyUY9';
    //密钥
    protected $key = '';
    //过期时间
    protected $expire = 7200;
    //当前token
    protected static $token = '';
    protected $model = '';
    protected $unique = false;
    public function __construct()
    {
        $key = config('admin.token_key', 'QoYEClMJsgOSWUBkSCq26yWkApqSuH3');
        $this->model = config('admin.token_model');
        $this->unique = config('admin.token_unique',false);
        $this->key = substr(md5($key), 8, 16);
        $this->expire = config('admin.token_expire', 7200);
    }

    /**
     * 退出token
     * @Author: rocky
     * 2019/12/7 13:37
     * @param $token
     * @return mixed
     */
    public function logout($token = '')
    {
        if (empty($token)) {
            return Cache::set(md5(self::$token), time(), $this->expire);
        } else {
            return Cache::set(md5($token), time(), $this->expire);
        }
    }

    /**
     * 获取当前token
     * @Author: rocky
     * 2019/11/4 16:31
     * @return $token
     */
    public function get()
    {
        return self::$token ? self::$token : Request::header('Authorization');
    }

    /**
     * 设置token
     * @Author: rocky
     * 2019/11/4 16:31
     * @param $token
     */
    public function set($token)
    {
        self::$token = $token;
        return true;
    }

    /**
     * 清除token
     * @Author: rocky
     * 2019/11/4 16:31
     * @param $token
     */
    public function clear()
    {
        self::$token = '';
        return true;
    }

    /**
     * 返回token
     * @Author: rocky
     * 2019/7/11 15:27
     * @param $data
     * @return array
     */
    public function encode($data)
    {
        $data['expire'] = time() + $this->expire;
        $str = json_encode($data);
        $token = openssl_encrypt($str, 'aes-256-cbc', $this->key, 0, self::IV);
        if (isset($data['id'])) {
            $cacheKey = 'last_auth_token_' . $data['id'];
            //开启唯一登录就将上次token加入黑名单
            if (Cache::has($cacheKey) && $this->unique) {
                $logoutToken = Cache::get($cacheKey);
                $this->logout($logoutToken);
            }
            //保存最新token
            Cache::set($cacheKey, $token, $this->expire);
        }
        return [
            'token' => $token,
            'expire' => (int)$this->expire
        ];
    }

    /**
     * 解密TOKEN
     * @Author: rocky
     * 2019/7/11 18:52
     * @param $token
     * @return string
     */
    public function decode($token = '')
    {
        if (empty($token)) {
            $token = Request::header('Authorization');
            if(Request::has('Authorization')){
                $token = rawurldecode(Request::get('Authorization'));
            }
        }
        $str = openssl_decrypt($token, 'aes-256-cbc', $this->key, 0, self::IV);
        if ($str === false) {
            return false;
        } else {
            return json_decode($str, true);
        }
    }

    /**
     * 刷新token
     * @param $token
     * @return array|bool
     */
    public function refresh($token = '')
    {
        $data = $this->decode($token);
        if ($data) {
            return $this->encode($data);
        } else {
            return false;
        }
    }

    /**
     * 验证token
     * @Author: rocky
     * 2019/7/12 17:12
     * @param $token 需要验证的token
     * @return bool|\think\response\Json 通过返回真
     */
    public function auth($token = null)
    {
        if(is_null($token)){
            $token = self::$token ? self::$token : Request::header('Authorization');
        }
        if (empty($token)) {
            $this->errorCode(4000, '请先登陆再访问');
        }
        $data = $this->decode($token);
        if ($data === false || Cache::has(md5($token))) {
            $this->errorCode(4001, '授权认证失败');
        }
        if ($data['expire'] < time()) {
            $this->errorCode(4002, '授权失效,身份过期');
        }
        return true;
    }

    /**
     * 获取Token保存数组key下的值
     * @Author: rocky
     * 2019/7/12 17:17
     * @param $name
     * @return string
     */
    public function getVar($name)
    {
        $data = $this->decode();
        if (isset($data[$name])) {
            return $data[$name];
        } else {
            return null;
        }
    }

    /**
     * 获取用户id
     * @Author: rocky
     * 2019/7/12 17:18
     * @return string
     */
    public function id()
    {
        return $this->getVar('id');
    }

    /**
     * 返回用户模型
     * @return mixed
     */
    public function user($lock = false)
    {

        if (is_null($this->id())) {
            return null;
        }
        $user = new $this->model;
        return $user->lock($lock)->find($this->id());
    }
}
