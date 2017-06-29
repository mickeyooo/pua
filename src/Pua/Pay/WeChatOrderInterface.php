<?php
namespace Pua\Pay;

interface WeChatOrderInterface
{
    /**
     * 商户编号
     *
     * @return string
     */
    function getMerchantId();

    /**
     * 总支付金额（分）
     *
     * @return int
     */
    function getTotalFee();

    /**
     * 微信订单号
     *
     * @return string
     */
    function getTransactionId();

    /**
     * 商户订单号
     *
     * @return string
     */
    function getTradeNo();

    /**
     * 交易状态
     *
     * @return int
     */
    function getTradeStatus();

}