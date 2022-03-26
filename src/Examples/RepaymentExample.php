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

//创建银行代收VA
function createBankVa(MonetapaySrv $monetapaySrv){
    $body = [
        'app_id'            => '6909',              //代收appid，由Monetapay提供
        'mch_order_no'      => '12345678910',       //客户内部的还款码ID，需确保唯一性
        'amount'            => '50000',             //还款码支付金额；如果是浮点数，应使用string类型
        'account_bank_code' => 'BNI',               //创建哪个银行的还款码
        'account_name'      => 'Mr Lucky',          //还款码支付人姓名
        'account_phone'     => '6285340417455',     //还款码支付人手机号
        'expire_seconds'    => 6*60*60,             //还款码有效时间，默认6h
        'is_single_use'     => 1,                   //是否是一次性还款码，一次性还款码只能支付固定amount金额，多次使用的还款码可支付amount以下的任意金额
        'custom_extra'      => 'Field value of type string that supposed carried in the request body while callback',
    ];

    $result = $monetapaySrv->createVA($body);

    print_r($result);
}

//查询VA及其支付信息，如果是静态VA可能存在多个时间跨度很大的支付记录，所以需要指明查询时间范围以及分页信息
function getVaInfo(MonetapaySrv $monetapaySrv){

    $va = $monetapaySrv->inquiryRepayment('12345678910'
        ,'2022-03-01 00:00:00'
        ,'2022-03-31 00:00:00'
        ,1
        ,30
    );

    print_r($va);
}


function handleCallback(MonetapaySrv $monetapaySrv){
    //回调传参，客户可自行通过各种方式获取
    $input = file_get_contents("php://input");

    //假定回调传参
    $input = '{
    "data": {
        "en_data": "LrzmDy7AwH\/mrlV1mQZCpXs++tC6+FH9JkS5LuOU17DhVfUxzuffLpEhOV1FZJf5t9MSBBzllYkkrLyc00spV2PYZKPcLozGcp0wxMJZxhBZlZnIbcEbe1\/xbT57bCRuoAlYwZRV1eH5hbzni0T4sxZ9VjzEiv4nN0i8J+5u3gQG8AoiI5sCjMQ5ekX1aDiX\/J6QabOj3zp7NJaBmfK3lShuCxr50wt\/RmOL1qr2P4tyqbSMAaY6b2D92q3BrYkCXuHqZc3mWSXSEMwA9ePa+hTmCDksElVaj6FfdThXaYKTKdXAp3Idu6SVjialsIYCd7L7TkzpvCqOKmUaJzYgAjXbMq5dfrpckUubvbfMWcKL9UclWs7omf1K4XlS516xG2nuo43Umz3WyTZN+JYVv2+9r1YOq\/ZoHlOVAY4i\/JEOxhuY0YxdeZUUyj70BD3RcNM5fQoVSSqvJnWmN3AeYLae5jjTRyyeZg5dC248EzVNQVPA3y4T72Mn77\/6VDJstVLgUrRxUZqpJ0sIVSTE0g==",
        "partner_key": "n4Lr1HZU2WVcJy8QdeQKT5jk8OSfafBu",
        "mch_order_no": "12345678910"
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