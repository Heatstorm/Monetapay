<?php

namespace Firestorm\Monetapay\Encrypt;

interface IEncr
{
    /**
     * @param $string
     * @param $key
     * @return mixed
     */
    public function encrypt($string,$key);

    /**
     * @param $string
     * @param $key
     * @return mixed
     */
    public function decrypt($string,$key);

}