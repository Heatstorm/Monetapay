<?php

namespace Firestorm\Monetapay;


use Firestorm\Monetapay\Encrypt\AesEncr;
use Firestorm\Monetapay\Encrypt\IEncr;
use Firestorm\Monetapay\Exceptions\RawBodyParseException;

class RawBuilder
{
    private $decrypt_delimiter = '__';
    private $kv_delimiter = '=';

    /**
     * @var string
     */
    private $partnerKey;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $aesKey;


    /**
     * @Inject
     * @var IEncr
     */
    private $encryptor;

    public function __construct($partnerKey,$token,$aesKey,$iv)
    {
        $this->partnerKey = $partnerKey;
        $this->token = $token;
        $this->aesKey = $aesKey;
        $this->encryptor = new AesEncr('AES-128-CBC',$iv);
    }

    public function parseApiPayload($body, array $headers = [])
    {
        if (is_array($body)){
            $payload  = $body;
        }elseif (is_string($body)){
            $payload = json_decode($body,true);
        }else{
            $payload = [];
        }

        $data = isset($payload['data'])?$payload['data']:[];
        $partnerKey = isset($data['partner_key'])?$data['partner_key']:'';
        $enData = isset($data['en_data'])?$data['en_data']:'';
        $mchOrderNo  = isset($data['mch_order_no'])?$data['mch_order_no']:'';

        //body格式错误
        if (empty($data)){
            throw new RawBodyParseException('Data empty');
        }

        //缺少data.partner_key
        if (empty($partnerKey)){
            throw new RawBodyParseException('Data must contain an non-empty partner_key');
        }

        //缺少data.en_data
        if (empty($enData)){
            throw new RawBodyParseException('Data must contain an non-empty en_data');
        }

        //data.en_data解析成明文串
        $decryptedStr = $this->encryptor->decrypt($enData,$this->aesKey);
        if (empty($decryptedStr)){
            throw new RawBodyParseException('en_data decrypted failed');
        }

        //请求参数数组k=>v
        $data = $this->formatDecrypt($decryptedStr);

        //验签
        $timestamp = isset($data['timestamp'])?$data['timestamp']:null;
        $clientSign = isset($data['sign'])?$data['sign']:null;
        if (empty($timestamp) || empty($clientSign)){
            throw new RawBodyParseException('timestamp or sign required');
        }
        $sign = $this->buildSignature($this->token,$timestamp,$data);
        if ($sign !== $clientSign){
            throw new RawBodyParseException('Invalid signature');
        }

        return $data;
    }

    public function buildApiPayload( array $body = [])
    {

        $timestamp = time();
        $sign = $this->buildSignature($this->token,$timestamp,$body);
        $body['timestamp'] = $timestamp;
        $body['sign'] = $sign;

        //明文
        $decString = $this->toString($body);

        //密文
        $enStr  =  $this->encryptor->encrypt($decString,$this->aesKey);

        return json_encode([
            'data' => [
                'partner_key' => $this->partnerKey,
                'en_data' => $enStr
            ],
        ]);
    }

    /**
     * 生成签名
     * @param $signToken    -签名token
     * @param $timestamp    -时间戳
     * @param array $body   -签名数据
     * @return string
     */
    private function buildSignature($signToken,$timestamp,array $body = []){
        unset($body['timestamp'],$body['sign']);
        ksort($body);

        $asString = $this->toString($body);
        $md5String = $signToken . '*|*' . $asString . '@!@' . $timestamp;
        return md5(md5($md5String));
    }

    /**
     * 解密后的明文串，解析成key=>value数组
     * @param $string
     * @return array
     */
    private function formatDecrypt($string)
    {
        //$kvs: amount=541654.15__bank_code=BNI__extra=__user_name=Lucky
        $kvs = explode($this->decrypt_delimiter,$string);
        $ret = [];
        foreach ($kvs as $kv){
            $kvArr = explode($this->kv_delimiter,$kv);
            if (empty($kvArr) || count($kvArr) < 2 ){
                continue;
            }

            $ret[$kvArr[0]]  = $kvArr[1];
        }

        return $ret;
    }

    /**
     * key=>value，组装字符串格式
     * @param array $body
     * @return string
     */
    private function toString(array $body = []){
        $string = '';
        foreach ($body as $k => $v){
            $string .= "{$k}{$this->kv_delimiter}{$v}";
            $string .= $this->decrypt_delimiter;
        }

        $string = rtrim($string,$this->decrypt_delimiter);

        return $string;
    }


}