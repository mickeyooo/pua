<?php

namespace Pua\Pay;

use Psr\Log\LoggerInterface;
use Pua\Net\HttpRequest;

class GzGhtApply extends PayAbstract
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $defaults = [
        'host'        => 'http://120.31.132.119:8082',
        'version'     => '1.0.0',
        'terminal_no' => '20000147',
    ];

    public function __construct(LoggerInterface $logger, array $options = [])
    {
        $this->logger = $logger;
        $options && $this->defaults = array_merge($this->defaults, $options);

        $this->defaults['rsaPrivateKey'] = '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCyabXKAEcacWk7
vRYAeFBOYbTMu9s/tWXE2EcZBglUNe4Pbx7zEwhNF8K6sWH53ooYtJFKVmk8xUgd
nxEdYucnFWG0QXFHwJpPptLXDZyLeRbJWVqAZ/JEXG2su9fSs4pCFUPySqhEyYBS
4KVy0HMUcqICH+4ia9dLvL6CApGLEAB7Ia3SVZSAFF2hLpkGWunM22x944eRY5hb
WnKMS2XP6FIeiODR7b32oLA4xCqn/CehCZ4ejMLgDpW1HDCLZd5gFQfIlnFLpjCK
SauhVYRJZPdsEY3kIuNPqWe6BGxfYYkopHQL94/M++49qTDSMYTpN1/fooyvfEhq
nR5ucQyXAgMBAAECggEBAIoy4jMHxgwQn3A7xqg0ihg9DPyt42ZVD/DLiz8x8tX9
NFtWOwYUzxBQgHF256rSm+wJKqYHi1scggEX7vzxWJZotJcZPjNTWPSsB5O3onRT
Jrhu83CVlA8p/XaYxtQaaNVJfalX9UHbSABqrR3jo9DJ/v5gV2joWgv7tyIj9TNe
ta11Ntqrn+ZrEMVadtntkZ8A1gAtHPc02lXlgV0KUzZVP9rK6B6f5YafNEemhHQ6
mYFfNhkPsgVBsGL/KzJ8kbN1xNsIB1rKNr36aPskxG+9ys60gbpyeMJhJuP3ekLd
m04c5WnF/DoCOqZgCwPH0a2l2Rv/aP1py/6SeFRYy1kCgYEA2VIxuEpHMsBBljih
TKEkWYfzgQp7/pocV8LCUV+pvFRdQaHDgOjz0ucnfmQGO5dFZhYmiUM+uP+pURgn
ss+ZnFKU+lBXNCAZaLry7yP5TSVYGibIkVIPUGhuYRHmjlKojUe8/7I7vTC9q3El
hhuAV2yTYxOsRfvm4D9U1SDBK10CgYEA0iq+Q+2YsvqvAQkyE87AscLHL8Vs/FHD
VCsqU0wmnYhj2ver3k5obm8bdrttiQZM1mPJR6gqmNiyVaZAUcWVCbV5mJ97HutE
EzwaJETFS5ZE/5635ts6GaI4PbGXHxXlbPXv0q24YiuIiJ560I+i1/aCItc5oON8
lsnOkApVjIMCgYA3Nv3w79ZVG6nOTAcXXB3LLZJ9p7dHQcqPtaj/WcnbUqf7A+mT
OByy6g4Lu8glndKBFIGoAFDQWgyf0P5NHRfPMuAtFPqDAODTziPpBH/TzPgsdMwi
t/GyIUZiHVUxteijNKXdZWBuOhMGmxHIl/YswCZWVuo/QbgwI4cfO1o49QKBgBuO
cS4U7C2jgui+3OsN8+Qa5uUTnMukqNjTZBRR6spDBNzEFqvqWfUI6m/x+VW7Fr4R
jWWw3gz2dMOYLdzK7FS+j7f8STdvn5hqC/9vaPMVO+zMUc6aNg8AXyFvtKHlzBQy
VwSntIJitN888FuCSdbJQpzw3WSED2TyBvyJ7lejAoGBANYBEo9qjP1RG5zM2rQC
jFlQxxVni2vNfNdi9zKkXS+ktD38ssVt9/XhZLwXTGpNITGqFGt4Hg1TBbj2g42P
x8YWzF6DoZINYE/tmlatvh4yLNQt+0P7UsRBpctVsg6W1/z6ULX79MotKBBXeEmd
bgss4zB4U97eL49M+psi9b3L
-----END PRIVATE KEY-----';
        $this->defaults['rsaPublicKey']  = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsmm1ygBHGnFpO70WAHhQ
TmG0zLvbP7VlxNhHGQYJVDXuD28e8xMITRfCurFh+d6KGLSRSlZpPMVIHZ8RHWLn
JxVhtEFxR8CaT6bS1w2ci3kWyVlagGfyRFxtrLvX0rOKQhVD8kqoRMmAUuClctBz
FHKiAh/uImvXS7y+ggKRixAAeyGt0lWUgBRdoS6ZBlrpzNtsfeOHkWOYW1pyjEtl
z+hSHojg0e299qCwOMQqp/wnoQmeHozC4A6VtRwwi2XeYBUHyJZxS6YwikmroVWE
SWT3bBGN5CLjT6lnugRsX2GJKKR0C/ePzPvuPakw0jGE6Tdf36KMr3xIap0ebnEM
lwIDAQAB
-----END PUBLIC KEY-----';
    }


    /**
     * 商户基础信息登记
     *
     * @param string $merchantName    商户名称
     * @param string $shortName       商户简称
     * @param string $merchantAddress 商户地址
     * @param string $servicePhone    客服电话
     * @param string $merchantType    商户类型
     * @param string $city            商户城市
     * @param string $category        经营类目
     * @param string $corpmanName     法人姓名
     * @param string $corpmanId       法人身份证
     * @param string $corpmanMobile   法人联系手机
     * @param string $bankCode        开户三位银行代码
     * @param string $bankName        开户行全称
     * @param string $bankAccountNo   开户行账号
     * @param string $bankAccountName 开户户名
     * @param int    $autoCus         自动提现
     * @param string $remark          备注
     * @param int    $handleType      操作类型 0:新增 1:修改。
     *
     * @return array
     */
    public function basic(
        $merchantName, $shortName, $merchantAddress, $servicePhone, $merchantType,
        $city, $category,
        $corpmanName, $corpmanId, $corpmanMobile,
        $bankCode, $bankName, $bankAccountNo, $bankAccountName,
        $autoCus = 1, $remark = '', $handleType = 0)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>  
  <head>   
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>100001</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate>  
  </head>  
  <body>   
    <handleType>' . $handleType . '</handleType>
    <merchantName>' . $merchantName . '</merchantName>   
    <shortName>' . $shortName . '</shortName>          
    <city>' . $city . '</city>   
    <merchantAddress>' . $merchantAddress . '</merchantAddress>   
    <servicePhone>' . $servicePhone . '</servicePhone>          
    <merchantType>' . $merchantType . '</merchantType>          
    <category>' . $category . '</category>          
    <corpmanName>' . $corpmanName . '</corpmanName>          
    <corpmanId>' . $corpmanId . '</corpmanId>          
    <corpmanMobile>' . $corpmanMobile . '</corpmanMobile>          
    <bankCode>' . $bankCode . '</bankCode>    
    <bankName>' . $bankName . '</bankName>    
    <bankaccountNo>' . $bankAccountNo . '</bankaccountNo>    
    <bankaccountName>' . $bankAccountName . '</bankaccountName>           
    <autoCus>' . $autoCus . '</autoCus>           
    <remark>' . $remark . '</remark>  
  </body> 
</merchant>';
        $this->logger->debug('Basic Info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '100001'
        ];
        $this->logger->info('Basic info request', $data);

        return $this->post('/interfaceWeb/basicInfo', $data);
    }

    /**
     * @param string $merchantId    基础信息登记接口 中的法人联系手机号
     * @param string $bankAccountNo 银行卡号
     *
     * @return \SimpleXMLElement
     */
    public function bankInfoQuery($merchantId, $bankAccountNo)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>100006</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate> 
  </head>
  <body>
    <merchantId>' . $merchantId . '</merchantId>
     <bankaccountNo>' . $bankAccountNo . '</bankaccountNo>
  </body>
</merchant>';
        $this->logger->debug('Query bank card info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '100006'
        ];
        $this->logger->info('Query bank card info', $data);

        return $this->post('/interfaceWeb/qryCardInfo', $data);
    }

    /**
     * 商户银行卡信息登记
     *
     * @param string $merchantId      法人手机号
     * @param string $bankCode        银行代码
     * @param int    $bankaccProp     账户属性
     * @param string $name            持卡人姓名
     * @param string $bankaccountNo   银行卡号
     * @param int    $bankaccountType 银行卡类型
     * @param string $certCode        办卡证件类型
     * @param string $certNo          证件号码
     * @param int    $default         是否为默认账户：0:否 1： 是
     * @param int    $operate         操作类型,0：新增 1：删除
     *
     * @return array
     */
    public function bank(
        $merchantId, $bankCode, $bankaccProp, $name, $bankaccountNo,
        $bankaccountType, $certCode, $certNo, $default = 1, $operate = 0)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>   
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>100002</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate>  
  </head>
  <body>
    <merchantId>' . $merchantId . '</merchantId>
    <handleType>' . $operate . '</handleType>
    <bankCode>' . $bankCode . '</bankCode>
    <bankaccProp>' . $bankaccProp . '</bankaccProp>
    <name>' . $name . '</name>
    <bankaccountNo>' . $bankaccountNo . '</bankaccountNo>
    <bankaccountType>' . $bankaccountType . '</bankaccountType>
    <defaultAcc>' . $default . '</defaultAcc> 
    <certCode>' . $certCode . '</certCode>
    <certNo>' . $certNo . '</certNo>
  </body> 
</merchant>';
        $this->logger->debug('Basic Info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '100002'
        ];
        $this->logger->info('Bank info request', $data);

        return $this->post('/interfaceWeb/bankInfo', $data);
    }

    /**
     * @param string $merchantId 法人手机号
     * @param int    $cycleValue 结算周期
     * @param array  $business
     * @param int    $handleType 操作类型，0：新增、1：修改、2：关闭业务、3：重新开通
     *
     * @return \SimpleXMLElement
     */
    public function business($merchantId, $cycleValue, array $business = [], $handleType = 0)
    {
        $businessList = '';

        foreach ($business as $b) {
            $businessList .= '<busiList>
  <busiCode>' . $b['code'] . '</busiCode>
  <futureRateType>' . $b['type'] . '</futureRateType>
  <futureRateValue>' . $b['value'] . '</futureRateValue>
</busiList>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>   
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>100003</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate>  
  </head>
  <body>
    <merchantId>' . $merchantId . '</merchantId>
    <handleType>' . $handleType . '</handleType>
    <cycleValue>' . $cycleValue . '</cycleValue>
    ' . $businessList . '
  </body>
</merchant>';
        $this->logger->debug('Business Info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '100003'
        ];
        $this->logger->info('Business info request', $data);

        return $this->post('/interfaceWeb/busiInfo', $data);
    }


    /**
     * 商户入驻结果查询
     *
     * @param string $merchantId 法人手机号
     *
     * @return \SimpleXMLElement
     */
    public function query($merchantId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>100004</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate> 
  </head>
  <body>
    <merchantId>' . $merchantId . '</merchantId>
  </body>
</merchant>';
        $this->logger->debug('Query Info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '100004'
        ];
        $this->logger->info('Query info request', $data);
        $response = $this->post('/interfaceWeb/qryBalanceInfo', $data);

        return [
            'merchantId' => (string)$response->body->merchantId,
            'balance'    => (float)$response->body->balanceAmount,
            'freeze'     => (float)$response->body->freezeAmount
        ];
    }

    /**
     * @param string $merchantId 法人手机号
     *
     * @return \SimpleXMLElement
     */
    public function balance($merchantId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>100005</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate> 
  </head>
  <body>
    <merchantId>' . $merchantId . '</merchantId>
  </body>
</merchant>';
        $this->logger->debug('Balance Info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '100005'
        ];
        $this->logger->info('Balance info request', $data);

        return $this->post('/interfaceWeb/qryBalanceInfo', $data);
    }

    /**
     * 代付接口(D+0)
     *
     * @param string  $orderId     订单号
     * @param string  $merchantId  子商户号(入驻法人手机号)
     * @param string  $bankCode    银行代码
     * @param string  $accountNo   银行卡号
     * @param string  $accountName 银行卡所有人姓名
     * @param float   $amount      金额（单位元）
     * @param float   $extraFee    额外手续费（单位元）
     * @param string  $identityNo
     * @param integer $accountProp 账户属性
     *
     * @return array
     * @throws \Exception
     */
    public function cashOut(
        $orderId,
        $merchantId, $bankCode, $accountNo, $accountName,
        $amount, $extraFee, $identityNo, $accountProp = 0)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>200001</tranCode>         
    <reqMsgId>' . $orderId . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate> 
  </head>
  <body>
    <business_code>B00302</business_code>
    <user_id>' . $merchantId . '</user_id>
    <bank_code>' . $bankCode . '</bank_code>
    <account_no>' . $accountNo . '</account_no>
    <account_name>' . $accountName . '</account_name>
    <account_prop>' . $accountProp . '</account_prop>
    <amount>' . $amount * 100 . '</amount>
    <extra_fee>' . $extraFee * 100 . '</extra_fee>
    <terminal_no>' . $this->defaults['terminal_no'] . '</terminal_no>
    <ID>' . $identityNo . '</ID>
  </body>
</merchant>';
        $this->logger->debug('Cash out info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '200001'
        ];
        $this->logger->info('Cash out request', $data);
        $response = $this->post('/interfaceWeb/realTimeDF', $data);

        if ($response->head->respType != 'S')
            throw new \Exception($response->head->respMsg);

        return [
            'cardholder' => (string)$response->body->account_name,
            'amount'     => (int)$response->body->amount / 100,
            'payTime'    => new \DateTime((string)$response->body->respDate),
        ];
    }

    /**
     * 代付结果查询
     *
     * @param string $merchantId
     * @param string $orderId
     *
     * @return array
     * @throws \Exception
     */
    public function cashOutQuery($merchantId, $orderId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<merchant>
  <head>
    <version>' . $this->defaults['version'] . '</version>   
    <agencyId>' . $this->defaults['agencyId'] . '</agencyId>   
    <msgType>01</msgType>   
    <tranCode>200002</tranCode>         
    <reqMsgId>' . date("Ymdhis") . mt_rand(1000, 9999) . '</reqMsgId>    
    <reqDate>' . date("Ymdhis") . '</reqDate> 
  </head>
  <body>
    <User_id>' . $merchantId . '</User_id>
    <Query_sn>' . $orderId . '</Query_sn>
  </body>
</merchant>';
        $this->logger->debug('Cash out query info', ['xml' => $xml]);

        $key  = self::buildNonce(16);
        $data = [
            'encryptData' => $this->encrypt($xml, $key),
            'encryptKey'  => $this->rsaEncrypt($key),
            'agencyId'    => $this->defaults['agencyId'],
            'signData'    => $this->getSign($xml),
            'tranCode'    => '200002'
        ];
        $this->logger->info('Cash out query request', $data);
        $response = $this->post('/interfaceWeb/queryResultDF', $data);

        if ($response->head->respType != 'S')
            throw new \Exception($response->head->respMsg);

        return [
            'merchantId'    => (string)$response->body->User_id,
            'accountNumber' => (string)$response->body->Account,
            'cardholder'    => (string)$response->body->Account_name,
            'amount'        => (int)$response->body->Amount,
            'payTime'       => new \DateTime((string)$response->body->respDate),
        ];
    }

    protected function post($url, $data)
    {
        $response = HttpRequest::post($this->defaults['host'] . $url, $data);

        foreach (explode('&', $response) as $p) {
            $p           = explode('=', $p);
            $data[$p[0]] = $p[1];
        }

        if (!isset($data['encryptKey']) || !isset($data['encryptData']) || !isset($data['signData']))
            throw new \Exception("Return data error");

        $key = $this->rsaDecrypt($data['encryptKey']);
        $xml = $this->decrypt($data['encryptData'], $key);
        $this->logger->info($url, ['response' => $response, 'xml' => $xml]);

        $signature    = base64_decode($data['signData']);
        $xml          = substr($xml, 0, strpos($xml, '</ipay>') + 8);
        $verifyResult = openssl_verify($xml, $signature, $this->defaults['rsaPublicKey']);

        if ($verifyResult != 1)
            throw new \Exception("Bad signature");

        return simplexml_load_string($xml);
    }

    protected function getSign($data)
    {
        $pi   = openssl_pkey_get_private($this->defaults['rsaPrivateKey']);
        $sign = '';

        openssl_sign($data, $sign, $pi);
        openssl_free_key($pi);

        return base64_encode($sign);
    }

    public function rsaEncrypt($data)
    {
        if (!$publicKey = openssl_pkey_get_public($this->defaults['rsaPublicKey']))
            return new \Exception("Public key invalid.");
        if (openssl_public_encrypt($data, $encryptData, $publicKey))
            return base64_encode($encryptData);

        throw new \Exception("RSA encrypt fail.");
    }

    public function rsaDecrypt($data)
    {
        if (!$privateKey = openssl_pkey_get_private($this->defaults['rsaPrivateKey']))
            throw new \Exception("Private key invalid.");
        if (openssl_private_decrypt(base64_decode($data), $decryptData, $privateKey))
            return $decryptData;

        throw new \Exception("RSA decrypt fail.");
    }

    protected function encrypt($data, $key)
    {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $data = $this->pkcs5_pad($data, $size);
        $td   = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv   = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        mcrypt_generic_init($td, $key, $iv);
        $encryptData = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return base64_encode($encryptData);
    }

    protected function decrypt($data, $key)
    {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($data), MCRYPT_MODE_ECB);
    }

    private function pkcs5_pad($text, $blockSize)
    {
        $pad = $blockSize - (strlen($text) % $blockSize);

        return $text . str_repeat(chr($pad), $pad);
    }

}