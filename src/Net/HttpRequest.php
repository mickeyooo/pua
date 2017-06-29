<?php
namespace Pua\Net;

class HttpRequest
{
    public static function get($url)
    {
        $ch = curl_init();

        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $content = curl_exec($ch);
        $status  = curl_getinfo($ch);
        $error   = curl_error($ch);
        curl_close($ch);

        if(intval($status["http_code"]) != 200)
            throw new \Exception($error);

        return $content;
    }

    /**
     * @param string       $url
     * @param string|array $data
     * @param int          $timeout
     * @param array        $headers
     * @param array        $ssl
     *
     * @return mixed
     * @throws \Exception
     */
    public static function post($url, $data, $timeout = 30, array $headers = [], array $ssl = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // 设置header
        $headers && curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // 证书
        if ($ssl && isset($ssl['cert']) && isset($ssl['key'])) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $ssl['cert']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $ssl['key']);
        }

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);

        if ($data = curl_exec($ch)) {
            curl_close($ch);

            return $data;
        } else {
            $code    = curl_errno($ch);
            $message = curl_error($ch);
            curl_close($ch);

            throw new \Exception($message, $code);
        }
    }
}