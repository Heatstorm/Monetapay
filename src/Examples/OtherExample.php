<?php
use Firestorm\Monetapay\MonetapaySrv;


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

//获取当前账号的余额信息
$result = $monetapaySrv->balance();
print_r($result);

//拉取账单
$result = $monetapaySrv->bills('2022-03-01'
    ,'2022-03-31 23:59:59'
    ,'',1,30);

print_r($result);