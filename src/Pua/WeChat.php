<?php
namespace Pua;

use Psr\Log\LoggerInterface;
use Pua\Net\HttpRequest;

class WeChat
{
    const HOST_OPEN = 'https://open.weixin.qq.com';
    const HOST_API = 'https://api.weixin.qq.com';

    private $logger;
    private $redis;
    private $appId;
    private $secret;


    public function __construct(array $config, \Redis $redis = null, LoggerInterface $logger = null)
    {
        if (!(isset($config['appid']) && isset($config['app_secret'])))
            throw new \InvalidArgumentException("$config 参数无效");

        $this->appId  = $config['appid'];
        $this->secret = $config['app_secret'];
        $redis && $this->redis = $redis;
        $logger && $this->logger = $logger;
    }

    /**
     * @param string $redirectUri 授权后重定向的回调链接地址
     * @param string $scope       应用授权作用域 snsapi_base|snsapi_userinfo
     * @param string $state       重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     *
     * @return string
     */
    public function getOauthRedirect($redirectUri, $scope = "snsapi_userinfo", $state = "")
    {
        return sprintf("%s/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect",
            self::HOST_OPEN,
            $this->appId,
            urlencode($redirectUri),
            $scope,
            $state);
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public function getOauthAccessToken($code)
    {
        $url = sprintf("%s/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code",
            self::HOST_API,
            $this->appId,
            $this->secret,
            $code);

        return json_decode(HttpRequest::get($url), true);
    }

    /**
     * 获取 access_token
     *
     * @return string
     * @throws \Exception
     */
    public function getAccessToken()
    {
        $key = 'wechat_access_token' . $this->appId;

        if ($token = $this->getCache($key))
            return $token;

        $url = sprintf("%s/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s",
            self::HOST_API, $this->appId, $this->secret);

        $result = json_decode($this->get($url, 'access token'), true);

        if (isset($result['errcode']))
            throw new \Exception($result['errmsg'], $result['errcode']);

        $expire = $result['expires_in'] ? intval($result['expires_in']) - 100 : 3600;
        $this->setCache($key, $result['access_token'], $expire);

        return $result['access_token'];
    }

    /**
     * 获取 jsapi_ticket
     *
     * @return string
     * @throws \Exception
     */
    public function getJsTicket()
    {
        $key = 'wechat_jsapi_ticket' . $this->appId;

        if ($ticket = $this->getCache($key))
            return $ticket;

        $url    = sprintf("%s/cgi-bin/ticket/getticket?access_token=%s&type=jsapi",
            self::HOST_API,
            $this->getAccessToken());
        $result = json_decode($this->get($url, 'js api ticket'), true);

        if (isset($result['errcode']) && !empty($result['errcode']))
            throw new \Exception($result['errmsg'], $result['errcode']);

        $expire = $result['expires_in'] ? intval($result['expires_in']) - 100 : 3600;
        $this->setCache($key, $result['ticket'], $expire);

        return $result['ticket'];
    }

    public function getJsSign($url)
    {
        $data              = [
            'timestamp'    => time(),
            'noncestr'     => $this->buildNonce(),
            'url'          => $url,
            'jsapi_ticket' => $this->getJsTicket()
        ];
        $data['signature'] = $this->buildSignature($data);
        $data['appId']     = $this->appId;

        return $data;
    }

    public function buildSignature(array $data)
    {
        $signature = "";

        ksort($data);
        foreach ($data as $k => $v) {
            $signature === "" && $signature .= "&";
            $signature .= $k . "=" . $v;
        }

        return sha1($signature);
    }

    public function buildNonce($length = 16)
    {
        $chars   = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $randMax = strlen($chars) - 1;
        $str     = "";

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $randMax)];
        }

        return $str;
    }

    protected function get($url, $message = '')
    {
        $result = HttpRequest::get($url);

        if ($this->logger)
            $this->logger->info($message, [
                'url'      => $url,
                'response' => $result
            ]);

        return $result;
    }

    protected function getCache($key)
    {
        if (!$this->redis)
            return false;

        return $this->redis->get($key);
    }

    protected function setCache($key, $value, $expire)
    {
        if (!$this->redis)
            return false;

        $this->redis->set($key, $value, $expire);
    }
}