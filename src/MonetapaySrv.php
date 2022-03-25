<?php

namespace Firestorm\Monetapay;

/**
 * Monetapay 支付
 * @link https://www.postman.com/sz-firestorm-it/workspace/monetapay-pub/api/6697f2d1-e151-483a-96a4-b02484aa7bb9
 */
class MonetapaySrv
{
    /**
     * @var RawBuilder
     */
    private $rawBuilder;

    /**
     * 接口域名
     * @var string
     */
    private $hostname;

    /**
     * 接口版本号
     * @var string
     */
    private $version;

    public function __construct($partnerKey,$token,$aesKey,$iv,$hostname,$version = 'v1.0.0')
    {
        $this->rawBuilder = new RawBuilder($partnerKey,$token,$aesKey,$iv);

        $this->hostname = $hostname;

        $this->version = $version;
    }

    /**
     * 发起代付
     * @param array $input
     * @return array
     */
    public function disbursement(array $input){
        $url = $this->hostname  .'/' . $this->version . '/disbursement';

        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'POST');
    }

    /**
     * 查询代付订单
     * @param $orderNo              -Monetapay 代付订单号
     * @param string $mchOrderNo    -客户代付订单号
     * @return array
     */
    public function inquiryDisbursement($orderNo = '',$mchOrderNo = ''){
        $url = $this->hostname  .'/' . $this->version . '/disbursement/query';

        $input = [
            'order_no' => $orderNo,
            'mch_order_no' => $mchOrderNo,
        ];
        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'GET');
    }

    /**
     * 创建银行VA
     * @param array $input
     * @return array
     */
    public function createVA(array $input){
        $url = $this->hostname  .'/' . $this->version . '/virtual_account';

        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'POST');
    }

    /**
     * 查询代收信息，及其在指定时间范围内的支付记录（静态VA可能有多次支付记录）
     * @param $orderNo          -Monetapay VA订单号/便利店payment code订单号/电子钱包link订单号
     * @param $startTime        -开始时间
     * @param $endTime          -截止时间
     * @param int $page         -当前页
     * @param int $pageSize     -每页拉取多少条支付记录
     * @return array
     */
    public function inquiryRepayment($orderNo,$startTime,$endTime,$page=1,$pageSize = 20){
        $url = $this->hostname  .'/' . $this->version . '/virtual_account/query';

        $input = [
            'order_no'      => $orderNo,
            'page'          => $page,
            'page_size'     => $pageSize,
            'start_time'    => $startTime,
            'end_time'      => $endTime,
        ];
        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'GET');
    }


    /**
     * 创建便利店payment code
     * @param array $input
     * @return array
     */
    public function createOTCPaymentCode(array $input){
        $url = $this->hostname  .'/' . $this->version . '/otc';

        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'POST');
    }

    /**
     * 创建电子钱包支付链接
     * @param array $input
     * @return array
     */
    public function createEWalletLink(array $input){
        $url = $this->hostname  .'/' . $this->version . '/ewallet';

        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'POST');
    }

    /**
     * 拉取所有流水账单
     * @param $startTime        -开始时间
     * @param $endTime          -截止时间
     * @param int $page         -当前页
     * @param int $pageSize     -每页拉取多少条流水账单
     * @return array
     */
    public function bills($startTime,$endTime,$page=1,$pageSize = 20){
        $url = $this->hostname  .'/' . $this->version . '/bills';

        $input = [
            'page'          => $page,
            'page_size'     => $pageSize,
            'start_time'    => $startTime,
            'end_time'      => $endTime,
        ];
        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'GET');
    }


    /**
     * 查询当前账户余额
     * @param string $currency  -货币单位
     * @return array
     */
    public function balance($currency = 'IDR'){
        $url = $this->hostname  .'/' . $this->version . '/balance';

        $input = [
            'currency' => $currency,
        ];
        //生成传参签名并加密
        $payload = $this->rawBuilder->buildApiPayload($input);

        return $this->request($url,$payload,'GET');
    }


    protected function request($url,$payload,$method,$headers = ['Content-Type: application/json']){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS =>$payload,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $asArr =  json_decode($response,true);

        return is_array($asArr)?$asArr:[];
    }

}