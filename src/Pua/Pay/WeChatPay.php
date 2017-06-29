<?php
namespace Pua\Pay;

use Pua\Net\HttpRequest;
use Psr\Log\LoggerInterface;

class WeChatPay extends PayAbstract implements WeChatMicroPayInterface, WeChatJsApiPayInterface, WeChatNativePayInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $tradeState = [
        'USERPAYING' => 1,
        'SUCCESS'    => 2,
        'PAYERROR'   => 3,
        'REFUND'     => 4,
        'NOTPAY'     => 5,
        'CLOSED'     => 6,
        'REVOKED'    => 7
    ];

    protected $defaults = [
        'sub_app_id' => '',
        'sub_mch_id' => '',
    ];

    protected $urlPrefix = 'https://api.mch.weixin.qq.com';

    public function __construct(array $config, LoggerInterface $logger)
    {
        if (!isset($config['appid']) && !isset($config['mch_id']) && !isset($config['api_secret']))
            throw new \InvalidArgumentException("$config 参数无效");

        $this->defaults = array_merge($this->defaults, $config);
        $this->logger   = $logger;
    }

    /**
     * 撤单
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay_sl.php?chapter=9_11&index=3
     *
     * @param $transactionId
     * @param $orderId
     *
     * @return array
     */
    public function cancel($transactionId, $orderId)
    {
        $data = [
            'appid'          => $this->defaults['appid'],
            'mch_id'         => $this->defaults['mch_id'],
            'transaction_id' => $transactionId,
            'out_trade_no'   => $orderId,
            'nonce_str'      => $orderId
        ];
        $this->defaults['sub_app_id'] && $data['sub_app_id'] = $this->defaults['sub_app_id'];
        $this->defaults['sub_mch_id'] && $data['sub_mch_id'] = $this->defaults['sub_mch_id'];
        $data['sign'] = self::getSign($data);
        $data         = self::toXml($data);

        $this->logger->info('order reverse request', ['xml' => $data]);
        $certPath = dirname(getcwd()) . '/cert/wechat';
        $response = $this->post($this->urlPrefix . '/secapi/pay/reverse', $data, [],
            ['key' => $certPath . '/apiclient_key.pem', 'cert' => $certPath . '/apiclient_cert.pem']);

        if (!$this->verifySign($response)) {
            throw new \InvalidArgumentException('Invalid sign.');
        }

        return $response;
    }

    /**
     * 退款查询
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay_sl.php?chapter=9_5
     *
     * @param string $transactionId 微信订单号
     * @param string $orderId       商户订单号
     * @param string $refundOrderId 商户退款单号
     * @param string $refundId      微信退款单号
     *
     * @return mixed
     */
    public function refundQuery($transactionId, $orderId = null, $refundOrderId = null, $refundId = null)
    {
        $data = [
            'appid'          => $this->defaults['appid'],
            'mch_id'         => $this->defaults['mch_id'],
            'nonce_str'      => self::buildNonce(16),
            'transaction_id' => $transactionId
        ];
        $orderId && $data['out_trade_no'] = $orderId;
        $refundOrderId && $data['out_refund_no'] = $refundOrderId;
        $refundId && $data['refund_id'] = $refundId;
        $this->defaults['sub_app_id'] && $data['sub_app_id'] = $this->defaults['sub_app_id'];
        $this->defaults['sub_mch_id'] && $data['sub_mch_id'] = $this->defaults['sub_mch_id'];
        $data['sign'] = self::getSign($data);
        $data         = self::toXml($data);

        $this->logger->info('order query request', ['xml' => $data]);
        $response = $this->post($this->urlPrefix . '/pay/refundquery', $data);

        if (!$this->verifySign($response)) {
            throw new \InvalidArgumentException('Invalid sign.');
        }

        return $response;
    }

    /**
     * 退款
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay_sl.php?chapter=9_4
     *
     * @param string $transactionId 微信订单号
     * @param string $orderId       商户订单号
     * @param int    $fee           订单金额
     * @param string $refundOrderId 商户退款单号
     * @param int    $refundFee     申请退款金额
     *
     * @return mixed
     */
    public function refund($transactionId, $orderId, $fee, $refundOrderId, $refundFee)
    {
        $data = [
            'appid'         => $this->defaults['appid'],
            'mch_id'        => $this->defaults['mch_id'],
            'nonce_str'     => self::buildNonce(16),
            'out_refund_no' => $refundOrderId,
            'total_fee'     => $fee,
            'refund_fee'    => $refundFee,
            'op_user_id'    => $this->defaults['mch_id']
        ];
        $transactionId && $data['transaction_id'] = $transactionId;
        $orderId && $data['out_trade_no'] = $orderId;
        $this->defaults['sub_app_id'] && $data['sub_app_id'] = $this->defaults['sub_app_id'];
        $this->defaults['sub_mch_id'] && $data['sub_mch_id'] = $this->defaults['sub_mch_id'];
        $data['sign'] = self::getSign($data);
        $data         = self::toXml($data);

        $this->logger->info('order refund request', ['xml' => $data]);
        $certPath = dirname(getcwd()) . '/cert/wechat';
        $response = $this->post($this->urlPrefix . '/pay/refund', $data, [],
            ['key' => $certPath . '/apiclient_key.pem', 'cert' => $certPath . '/apiclient_cert.pem']);

        if (!$this->verifySign($response)) {
            throw new \InvalidArgumentException('Invalid sign.');
        }

        return $response;
    }

    /**
     * 查询订单状态
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay_sl.php?chapter=9_2
     *
     * @param string $transactionId
     * @param string $orderId
     *
     * @return array
     */
    public function query($transactionId, $orderId = null)
    {
        $data = [
            'appid'     => $this->defaults['appid'],
            'mch_id'    => $this->defaults['mch_id'],
            'nonce_str' => self::buildNonce(16),
        ];
        $transactionId && $data['transaction_id'] = $transactionId;
        $orderId && $data['out_trade_no'] = $orderId;
        $this->defaults['sub_app_id'] && $data['sub_app_id'] = $this->defaults['sub_app_id'];
        $this->defaults['sub_mch_id'] && $data['sub_mch_id'] = $this->defaults['sub_mch_id'];
        $data['sign'] = self::getSign($data);
        $data         = self::toXml($data);

        $this->logger->info('order query request', ['xml' => $data]);
        $response = $this->post($this->urlPrefix . '/pay/orderquery', $data);

        if (!$this->verifySign($response)) {
            throw new \InvalidArgumentException('Invalid sign.');
        }

        return $response;
    }

    /**
     * 生成微信扫码支付订单
     * @link https://pay.weixin.qq.com/wiki/doc/api/native_sl.php?chapter=6_5
     *
     * @param string $orderId
     * @param string $body
     * @param int    $fee
     * @param string $notifyAbsoluteUrl
     * @param string $ip
     * @param int    $timeExpire
     * @param array  $options
     *
     * @return string 支付url
     */
    public function buildNativeOrder($orderId, $body, $fee, $notifyAbsoluteUrl,
                                     $ip, $timeExpire = 30, array $options = [])
    {
        $data = $this->buildUnifiedOrderData($orderId, $body, $fee, $notifyAbsoluteUrl, '',
            $ip, 'NATIVE', $timeExpire, $options);

        $this->logger->info('Build native order request', ['xml' => $data]);
        $response = $this->post($this->urlPrefix . '/pay/unifiedorder', $data);

        return $response['code_url'];
    }

    /**
     * 生成微信公众号支付订单
     *
     * {@inheritdoc}
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=7_1
     */
    public function buildJsApiOrder($orderId, $body, $fee, $notifyAbsoluteUrl, $openId,
                                    $ip, $timeExpire = 30, array $options = [])
    {
        $data = $this->buildUnifiedOrderData($orderId, $body, $fee,
            $notifyAbsoluteUrl, $openId, $ip, 'JSAPI', $timeExpire, $options);

        $this->logger->info('Build jsapi order request', ['xml' => $data]);
        $response = $this->post($this->urlPrefix . '/pay/unifiedorder', $data);

        return $this->_buildPrepayQueryParameters($response['prepay_id']);
    }

    private function _buildPrepayQueryParameters($prepayId)
    {
        $data              = [
            "appId"     => $this->defaults['appid'],
            "timeStamp" => time(),
            "nonceStr"  => self::buildNonce(16),
            "package"   => "prepay_id=$prepayId",
            "signType"  => "MD5"
        ];
        $data["paySign"]   = $this->getSign($data);
        $data['timestamp'] = $data['timeStamp'];
        unset($data['timeStamp']);

        return $data;
    }

    private function buildUnifiedOrderData($orderId, $body, $fee,
                                           $notifyAbsoluteUrl, $openId, $ip,
                                           $tradeType, $timeExpire, array $options = [])
    {
        $data = [
            'appid'            => $this->defaults['appid'],
            'mch_id'           => $this->defaults['mch_id'],
            'nonce_str'        => self::buildNonce(16),
            'body'             => $body,
            'out_trade_no'     => $orderId,
            'total_fee'        => $fee,
            'spbill_create_ip' => $ip,
            'notify_url'       => $notifyAbsoluteUrl,
            'trade_type'       => $tradeType,
            'openid'           => $openId,
            'time_expire'      => date('YmdHis', time() + (int)$timeExpire)
        ];

        $openId && $data['openid'] = $openId;
        $this->defaults['sub_app_id'] && $data['sub_app_id'] = $this->defaults['sub_app_id'];
        $this->defaults['sub_mch_id'] && $data['sub_mch_id'] = $this->defaults['sub_mch_id'];
        $options && $data = array_merge($data, $options);
        $data['sign'] = self::getSign($data);
        $data         = self::toXml($data);

        return $data;
    }

    /**
     * 支付结果通用通知
     *
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=9_7
     *
     * @param string $data
     *
     * @return array
     */
    public function notify($data)
    {
        $this->logger->info("notify Response", ['xml' => $data]);
        $data = self::fromXml($data);

        if (!$this->verifySign($data)) {
            throw new \InvalidArgumentException('Invalid sign.');
        }

        return $data;
    }

    /**
     * 微信刷卡支付
     *
     * {@inheritdoc}
     * @link https://pay.weixin.qq.com/wiki/doc/api/micropay_sl.php?chapter=5_1
     */
    public function microPay($orderId, $title, $body, $fee, $authCode, $ip, array $options = [])
    {
        $data = [
            'appid'            => $this->defaults['appid'],
            'mch_id'           => $this->defaults['mch_id'],
            'nonce_str'        => self::buildNonce(16),
            'body'             => $title,
            'out_trade_no'     => $orderId,
            'total_fee'        => $fee,
            'spbill_create_ip' => $ip,
            'auth_code'        => $authCode,
        ];
        $this->defaults['sub_app_id'] && $data['sub_app_id'] = $this->defaults['sub_app_id'];
        $this->defaults['sub_mch_id'] && $data['sub_mch_id'] = $this->defaults['sub_mch_id'];
        $body && $data['detail'] = $body;
        $options && $data = array_merge($data, $options);
        $data['sign'] = self::getSign($data);
        $data         = self::toXml($data);

        $this->logger->info('micro pay request', ['xml' => $data]);
        $response = $this->post($this->urlPrefix . '/pay/micropay', $data);

        if (!$this->verifySign($response)) {
            throw new \InvalidArgumentException('Invalid sign.');
        }

        return $response;
    }

    /**
     * 获取请求参数签名
     *
     * @link https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=4_3
     *
     * @param array $params
     *
     * @return string
     */
    public function getSign(array $params)
    {
        $queryString = $this->buildSignQueryString($params);

        return strtoupper(md5($queryString . "&key=" . $this->defaults['api_secret']));
    }

    /**
     * {@inheritdoc}
     */
    public function verifySign(array $data)
    {
        if (!isset($data['sign']) || empty($data['sign']))
            throw new \InvalidArgumentException("parameter sign invalid");

        $sign = $data['sign'];
        unset($data['sign']);

        return $this->getSign($data) == $sign;
    }

    protected function post($url, $xml, array $headers = [], array $ssl = [])
    {
        $xml = HttpRequest::post($url, $xml, $headers, $ssl);

        $ssl && $xml = substr($xml, stripos($xml, '<xml>'));
        $this->logger->info('wechat response data', ['body' => $xml]);

        if (!$data = self::fromXml($xml))
            throw new \Exception('nothing data.');
        if (!isset($data['return_code']))
            throw new \Exception('return_code not found.');
        if ($data['return_code'] !== 'SUCCESS')
            throw new \Exception($data['return_msg']);
        if ($data['result_code'] !== 'SUCCESS')
            throw new \Exception($data['err_code_des']);
        if (isset($data['err_code']) && 'SUCCESS' != $data['err_code'])
            throw new \Exception($data['err_code_des']);

        return $data;
    }

    /**
     * 输出xml字符
     *
     * @param array $data
     *
     * @return xml
     **/
    public static function toXml(array $data)
    {
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";

        return $xml;
    }

    /**
     * 输出对象或数组
     *
     * @param string $xml
     * @param bool   $toArray
     * @param string $className 返回对象名
     *
     * @return mixed
     */
    public static function fromXml($xml, $toArray = true, $className = 'SimpleXMLElement')
    {
        $data = simplexml_load_string($xml, $className, LIBXML_NOCDATA);

        if ($toArray) {
            $data = json_decode(json_encode($data), true);
        }

        return $data;
    }
}