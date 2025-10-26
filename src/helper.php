<?php

if (!function_exists('http')) {
    /**
     * @return Maksym\Wget\WgetDriver
     */
    function http()
    {
        return Maksym\Wget\WgetDriver::init();
    }
}

if (!function_exists('httpClient')) {
    /**
     * @return Maksym\Wget\WgetDriver
     */
    function httpClient()
    {
        return Maksym\Wget\WgetDriver::init();
    }
}