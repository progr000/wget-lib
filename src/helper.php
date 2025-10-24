<?php
if (!function_exists('http')) {
    /**
     * @return Wget\WgetDriver
     */
    function http()
    {
        return Wget\WgetDriver::init();
    }
}

if (!function_exists('httpClient')) {
    /**
     * @return Wget\WgetDriver
     */
    function httpClient()
    {
        return Wget\WgetDriver::init();
    }
}