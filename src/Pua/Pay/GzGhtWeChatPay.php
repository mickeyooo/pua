<?php
namespace Pua\Pay;

class GzGhtWeChatPay extends GzGhtPay
{
    public function gatewayPay(
        $orderId, $body, $fee, $returnUrl, array $options = [])
    {
        $notifyUrl = isset($options['notify_url']) ? $options['notify_url'] : null;
        $data      = $this
            ->buildGatewayRequestData($orderId, $fee, $body, '', $returnUrl, 'PUBLICWECHAT', $notifyUrl, null, $options);
        $this->logger->info('PAY WECHAT request', $data);

        return ['payUrl' => $this->defaults['host'] . '/entry.do?' . http_build_query($data)];
    }

    public function microPay($orderId, $title, $body, $fee, $authCode, array $options = [])
    {
        $data = $this->buildNativeOrMicroRequestData(
            'BACKSTAGE_PAY',
            $orderId, 'BACKSTAGEWECHAT',
            $authCode, $fee, $title, $body, $options['notify_url']);

        $this->logger->info('BACKSTAGE_PAY WECHAT request', $data);
        $response = $this->post('/backStageEntry.do', $data);

        if ($response['pay_result'] != 1)
            throw new \Exception($response['resp_desc']);

        return [
            'merchantNo'    => $response['merchant_no'],
            'orderNumber'   => $response['order_no'],
            'transactionId' => $response['pay_no'],
            'amount'        => $response['amount'],
            'payTime'       => new \DateTime($response['pay_time']),
        ];
    }

    /**
     * 正扫支付
     *
     * @param string $orderId
     * @param string $body
     * @param float  $fee
     * @param string $notifyAbsoluteUrl
     * @param string $ip
     * @param int    $timeExpire
     * @param array  $options
     *
     * @return array
     */
    public function buildNativeOrder($orderId, $body, $fee, $notifyAbsoluteUrl, $ip,
                                     $timeExpire = 30, array $options = [])
    {
        $data = $this->buildNativeOrMicroRequestData(
            'FRONT_PAY',
            $orderId, 'WECHAT',
            null, $fee, $body, $body, $notifyAbsoluteUrl, $ip);

        $this->logger->info('FRONT_PAY WECHAT request', $data);
        $response = $this->post('/backStageEntry.do', $data);

        return [
            'orderId'       => $response['order_no'],
            'transactionId' => $response['pay_no'],
            'codeUrl'       => $response['qr_code']
        ];
    }
}