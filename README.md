# Monetapay

#### Requirement

- php>=5.3.0
- curl拓展
- openssl拓展

#### postman

- [Monetapay Api](https://www.getpostman.com/collections/9369895c623fe4f6f3f1 "Monetapay Api")

#### Install

- composer require firestorm/monetapay

#### Usage

- 初始化

```injectablephp

<?php
use Firestorm\Monetapay\MonetapaySrv;

//以下为开发参数，由Monetapay提供给客户
$partnerKey = 'n4Lr1HZU2WVcJy8QdeQKT5jk8OSfafBu';
$signToken  = 'VNSULBWPPESGAZJHSWTXLXWLYVNENAUT';
$aesKey     = 'UTYKkFlZl1WGOpRu';
$iv         = 'abc123rty456nji7';

//Monetapay基础服务接口域名，区分环境
$hostname = 'http://sandbox-api.monetapay.net';
//接口版本号
$version = 'v1.0.0';

$monetapaySrv = new MonetapaySrv($partnerKey,$signToken,$aesKey,$iv,$hostname,$version);

```

- 代付（转账）

```injectablephp
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

#result as one of fail cases
Array
(
    [code] => 4001
    [message] => mch_order_no duplicated
    [data] => Array
        (
            [_empty_placeholder] =>
        )

)

#result as success case
Array
(
    [code] => 0
    [message] => success
    [data] => Array
        (
            [mch_id] => 10004
            [order_no] => 20220326366244153695272960
            [mch_order_no] => 1234567891
            [status] => 0
            [amount] => 30000
            [account_bank_code] => BNI
            [account_name] => Dewi Haryono Putri
            [error_code] => 7101
            [error_msg] => Processing
            [order_time] => 2022-03-26 14:23:30
        )

)
```

- 查询代付订单信息

```injectablephp

 //Monetapay 代付订单号
$order_no = '';
//客户代付订单号
$mch_order_no = '123456789';

//order_no 与 mch_order_no必传其一，建议使用order_no查询
$result = $monetapaySrv->inquiryDisbursement($order_no,$mch_order_no);

print_r($result);

#result as successful disbursement
Array
(
    [code] => 0
    [message] => success
    [data] => Array
        (
            [mch_id] => 10004
            [order_no] => 20220326366242954828972032
            [mch_order_no] => 123456789
            [status] => 1
            [amount] => 30000.0000
            [account_bank_code] => BNI
            [account_name] => Dewi Haryono Putri
            [error_code] => 7116
            [error_msg] => balance exception
            [order_time] => 2022-03-26 14:18:44
        )

)

```

- 创建银行代收VA（virtual account）

```injectablephp
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

# result as created successfully VA
Array
(
    [code] => 0
    [message] => success
    [data] => Array
        (
            [order_no] => 20220326366248399996063744
            [mch_order_no] => 12345678910
            [account_bank_code] => BNI
            [amount] => 50000
            [virtual_account] => 9881704822032601
            [expiration_date] => 2022-03-26 20:40:23
            [is_open] => 1
            [is_single_use] => 1
        )

)
```

#### Callback

- 初始化参数解析

```injectablephp

```