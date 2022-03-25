<?php

namespace Firestorm\Monetapay\Encrypt;

class AesEncr implements IEncr
{
    private $method;

    private $iv;

    public function __construct($method,$iv)
    {
        $this->method = $method;
        $this->iv = $iv;
    }

    public function encrypt($string, $key)
    {
        return $secret_str = openssl_encrypt($string, $this->method, $key, 0, $this->iv);
    }

    public function decrypt($string, $key)
    {
        return $secert_str = openssl_decrypt($string, $this->method, $key, 0, $this->iv);
    }
}