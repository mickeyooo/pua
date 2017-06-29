<?php
namespace Pua\Pay;

interface WeChatMicroPayInterface
{
    /**
     * 刷卡支付
     *
     * @param string $orderId  订单号
     * @param string $title    订单标题
     * @param string $body     商品或支付单简要描述
     * @param int    $fee      订单总金额，单位为分
     * @param string $authCode 扫码支付授权码
     * @param string $ip       用户端ip
     * @param array  $options  其它可选支付参数
     *
     * @return array
     */
    function microPay($orderId, $title, $body, $fee, $authCode, $ip, array $options = []);
}