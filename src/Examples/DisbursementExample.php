<?php
use Firestorm\Monetapay\MonetapaySrv;
use Firestorm\Monetapay\Exceptions\RawBodyParseException;


//以下为开发参数，由Monetapay提供给客户
$partnerKey = 'n4Lr1HZU2WVcJy8QdeQKT5jk8OSfafBu';
$signToken = 'VNSULBWPPESGAZJHSWTXLXWLYVNENAUT';
$aesKey = 'UTYKkFlZl1WGOpRu';
$iv = 'abc123rty456nji7';

//Monetapay基础服务接口域名，区分测试环境与生产环境
$hostname = 'http://sandbox-api.monetapay.net';
//接口版本号
$version = 'v1.0.0';
$monetapaySrv = new MonetapaySrv($partnerKey,$signToken,$aesKey,$iv,$hostname,$version);


//代付测试
function disbursement(MonetapaySrv $monetapaySrv){

    $body = [
        'app_id'            => '6908',              //代付appid，由Monetapay提供
        'mch_order_no'      => '123456789',         //客户内部的代付订单号，需确保唯一性
        'amount'            => '30000',             //代付金额，如果是浮点数，应使用string类型
        'account_name'      => 'Dewi Haryono Putri',//持卡人姓名
        'account_bank_code' => 'BNI',               //转账目标银行代码
        'account_number'    => '0193374939',        //转账目标银行卡号
        'account_phone'     => '6285340417455',     //持卡人绑定的手机号
        'custom_extra'      => 'Field value of type string that supposed carried in the request body while callback',
    ];

    $result = $monetapaySrv->disbursement($body);

    print_r($result);
}

//查询代付订单
function inquiry(MonetapaySrv $monetapaySrv){

    //Monetapay 代付订单号
    $order_no = '';
    //客户代付订单号
    $mch_order_no = '123456789';

    //order_no 与 mch_order_no必传其一，建议使用order_no查询
    $result = $monetapaySrv->inquiryDisbursement($order_no,$mch_order_no);

    print_r($result);
}


function handleCallback(MonetapaySrv $monetapaySrv){
    //回调传参，客户可自行通过各种方式获取
    $input = file_get_contents("php://input");

    //假定回调传参
    $input = '{
    "data": {
        "en_data": "LrzmDy7AwH\/mrlV1mQZCpViLhJjc4CrJAtwNmTgpnBayZF2fFQtLzPWt\/gNg+7Ckd72uCDq94xn8iVszt4AARVRl8tAxXZDD\/G9SJ2ksC85LLpXEzE14FCNs4oEeFikBmrcPWBW5Ix9ZtGgRJA8eHE3tPokMYHGLt8+Hr\/LXeXx6H2UvBRaOGxhDIcaBWKsOluEVRAiUKydl2vSvuTZRFLcepuwf0VGlN3X\/oKLcIzudB0KvyCxb18MWzZlsRKrrnurS+3q77GSLS4MIoUXc6MwHuJVj302DCfUqexcUq8nflbPJkvXAtztCdaJ\/siBMq1jck5KTk2LIG7Di6kl\/bw60fXxklbVBwqruRGl+n58rn3twKY4errZc98w0p\/+EMJF99pXcYQHz8a06RgSyehuDwMejSiULEpOYb2ScgU8jErLl4eqXL0cUdSmQtbtBqf0bGV8tuquonk5F41a+3ZK4b6VC+0MCePJM0grssBhWzqP+A5lJOOmqNqdnZfZAyeWyaohvXLIjU7ghSrqviw==",
        "partner_key": "n4Lr1HZU2WVcJy8QdeQKT5jk8OSfafBu",
        "mch_order_no": "123456789"
    }
}';
    try {
        //解析后的明文body
        $data = $monetapaySrv->getRawBuilder()->parseApiPayload($input);
        print_r($data);
    }catch (RawBodyParseException $e){
        print_r("callback input parsed and decrypt fail, msg:{$e->getMessage()}");
    }
}