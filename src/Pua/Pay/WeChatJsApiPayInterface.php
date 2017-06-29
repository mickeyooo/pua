<?php
namespace Pua\Pay;

interface WeChatJsApiPayInterface extends WeChatNotifyInterface
{
    /**
     * 生成公众号支付订单
     *
     * @param string $orderId           待支付订单号
     * @param string $body              商品或支付单简要描述
     * @param int    $fee               订单总金额，单位为分
     * @param string $notifyAbsoluteUrl 接收微信支付异步通知回调地址
     * @param string $openId            微信支付帐号openid
     * @param string $ip                用户端ip
     * @param int    $timeExpire        支付超时时间，单位秒
     * @param array  $options           扩展项数据
     *
     * @return array
     */
    function buildJsApiOrder($orderId, $body, $fee, $notifyAbsoluteUrl, $openId,
                        $ip, $timeExpire = 30, array $options = []);
}