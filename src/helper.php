<?php

if (!function_exists('http')) {
    /**
     * @param array $curl_setopt
     * @return Maksym\Wget\WgetDriver
     */
    function http($curl_setopt = array())
    {
        return Maksym\Wget\WgetDriver::init($curl_setopt);
    }
}

if (!function_exists('httpClient')) {
    /**
     * @param array $curl_setopt
     * @return Maksym\Wget\WgetDriver
     */
    function httpClient($curl_setopt = array())
    {
        return Maksym\Wget\WgetDriver::init($curl_setopt);
    }
}