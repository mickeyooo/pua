<?php
namespace Pua\Pay;

interface WeChatNotifyInterface
{
    /**
     * 支付结果通用通知
     *
     * @param string    $data
     *
     * @return array|WeChatOrderInterface
     */
    public function notify($data);
}