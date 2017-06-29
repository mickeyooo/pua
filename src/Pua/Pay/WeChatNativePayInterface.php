<?php
namespace Pua\Pay;

interface WeChatNativePayInterface extends WeChatNotifyInterface
{
    /**
     * 生成扫码支付订单
     *
     * @param string $orderId           待支付订单号
     * @param string $body              商品或支付单简要描述
     * @param int    $fee               订单总金额，单位为分
     * @param string $notifyAbsoluteUrl 接收微信支付异步通知回调地址
     * @param string $ip                发起支付服务器IP
     * @param int    $timeExpire        支付超时时间，单位秒
     * @param array  $options           扩展项数据
     *
     * @return string 支付url
     */
    function buildNativeOrder($orderId, $body, $fee, $notifyAbsoluteUrl, $ip,
                              $timeExpire = 30, array $options = []);
}