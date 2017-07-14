<?php
namespace Pua\Pay;

use Psr\Log\LoggerInterface;
use Pua\Net\HttpRequest;

class GzGhtPay extends PayAbstract
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $defaults = [
        'host'        => 'http://test.pengjv.com',
        'merchant_no' => '102100000125',
        'terminal_no' => '20000147',
        'secret_key'  => '857e6g8y51b5k365f7v954s50u24h14w'
    ];

    public function __construct(LoggerInterface $logger, array $options = [])
    {
        $this->logger = $logger;
        $options && $this->defaults = array_merge($this->defaults, $options);
    }

    public function notify(array $data)
    {
        if (!$this->verifySign($data))
            throw new \Exception('Signature invalid');

        if ($data['pay_result'] == 1) {
            isset($data['pay_time']) && $data['pay_time'] = new \DateTime($data['pay_time']);

            return $data;
        } else
            throw new \Exception("Payment failure");
    }

    /**
     * 退款
     *
     * @param string $orderId       商户系统订单号
     * @param float  $fee           订单总金额
     * @param string $refundOrderId 商户系统退款单号
     * @param float  $refundFee     退款金额
     *
     * @return array
     * @throws \Exception
     */
    public function refund($orderId, $fee, $refundOrderId, $refundFee)
    {
        $data         = [
            'busi_code'          => 'REFUND',
            'merchant_no'        => $this->defaults['merchant_no'],
            'terminal_no'        => $this->defaults['terminal_no'],
            'order_no'           => $orderId,
            'refund_no'          => $refundOrderId,
            'total_amount'       => $fee,
            'refund_amount'      => $refundFee,
            'currency_type'      => 'CNY',
            'sett_currency_type' => 'CNY',
            'sign_type'          => 'SHA256',
        ];
        $data['sign'] = $this->getSign($data);

        $this->logger->info('Refund request', $data);
        $response = $this->post('/entry.do', $data);

        if ($response['refund_result'] !== 1)
            throw new \Exception($response['resp_desc']);

        return [
            'orderNumber'       => $response['refund_no'],
            'originOrderNumber' => $response['order_no'],
            'transactionId'     => $response['refund_id'],
            'refundAmount'      => $response['refund_amount']
        ];
    }

    /**
     * 订单查询
     *
     * @param string $orderId
     *
     * @return array
     */
    public function query($orderId)
    {
        $data         = [
            'busi_code'   => 'SEARCH',
            'merchant_no' => $this->defaults['merchant_no'],
            'terminal_no' => $this->defaults['terminal_no'],
            'order_no'    => $orderId,
            'sign_type'   => 'SHA256',
        ];
        $data['sign'] = $this->getSign($data);

        $this->logger->info('Query request', $data);
        $response = $this->post('/entry.do', $data);

        return $response;
    }

    protected function buildGatewayRequestData(
        $orderNo, $amount, $productName, $productDesc,
        $returnUrl, $bankCode = null, $notifyUrl = null, $clientIp = null, $options = [])
    {
        $data = [
            'busi_code'          => 'PAY',
            'merchant_no'        => $this->defaults['merchant_no'],
            'child_merchant_no'  => $this->defaults['child_merchant_no'],
            'terminal_no'        => $this->defaults['terminal_no'],
            'order_no'           => $orderNo,
            'amount'             => $amount,
            'currency_type'      => 'CNY',
            'sett_currency_type' => 'CNY',
            'product_name'       => $productName,
            'return_url'         => $returnUrl,
            'sign_type'          => 'SHA256'
        ];
        $productDesc && $data['product_desc'] = $productDesc;
        $notifyUrl && $data['notify_url'] = $notifyUrl;
        $clientIp && $data['client_ip'] = $clientIp;
        $bankCode && $data['bank_code'] = $bankCode;
        isset($options['openid']) && $data['user_bank_card_no'] = $options['openid'];
        $data['sign'] = $this->getSign($data);

        return $data;
    }

    /**
     * 生成正反扫支付请求据
     *
     * @param string $busiCode    业务代码
     * @param string $orderNo     商户订单号
     * @param string $bankCode    银行直连参数
     * @param string $authCode    授权码
     * @param float  $amount      订单金额
     * @param string $productName 产品名称
     * @param string $productDesc 产品描述
     * @param string $notifyUrl   异步通知地址
     * @param string $clientIp    订单创建ip
     * @param string $signType    签名类型
     *
     * @return array
     */
    protected function buildNativeOrMicroRequestData(
        $busiCode, $orderNo, $bankCode, $authCode,
        $amount, $productName, $productDesc, $notifyUrl, $clientIp = null, $signType = 'SHA256')
    {
        $data = [
            'busi_code'          => $busiCode,
            'merchant_no'        => $this->defaults['merchant_no'],
            'child_merchant_no'  => $this->defaults['child_merchant_no'],
            'terminal_no'        => $this->defaults['terminal_no'],
            'order_no'           => $orderNo,
            'bank_code'          => $bankCode,
            'amount'             => $amount,
            'currency_type'      => 'CNY',
            'sett_currency_type' => 'CNY',
            'product_name'       => $productName,
            'product_desc'       => $productDesc,
            'notify_url'         => $notifyUrl,
            'sign_type'          => $signType
        ];
        $authCode && $data['auth_code'] = $authCode;
        $clientIp && $data['client_ip'] = $clientIp;
        $data['sign'] = $this->getSign($data);

        return $data;
    }

    protected function post($url, $data)
    {
        $response = HttpRequest::post($this->defaults['host'] . $url, $data);
        $this->logger->info($url, ['xml' => $response]);

        $response = $this->fromXml($response);

        if ('00' != $response['resp_code'])
            throw new \Exception($response['resp_desc']);
        if (!$this->verifySign($response))
            throw new \Exception('Signature invalid');

        return $response;
    }

    private function fromXml($xml)
    {
        $data = simplexml_load_string($xml);

        return json_decode(json_encode($data), true);
    }

    private function verifySign($data)
    {
        $sign = $data['sign'];
        unset($data['sign']);

        return $this->getSign($data) == $sign;
    }

    /**
     * 生成 hash 签名
     *
     * @param array $data
     *
     * @return string
     */
    protected function getSign(array $data)
    {
        $queryString = $this->buildSignQueryString($data) . "&key=" . $this->defaults['secret_key'];

        return hash("sha256", $queryString);
    }

}