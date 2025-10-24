<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @return void
     */
    public function testHttp()
    {
        $ret = http();
        $this->assertContains('WgetDriver', get_class($ret));
        $this->assertTrue(method_exists($ret, 'post'));
    }

    /**
     * @return void
     */
    public function testHttpClient()
    {
        $ret = httpClient();
        $this->assertContains('WgetDriver', get_class($ret));
        $this->assertTrue(method_exists($ret, 'post'));
    }
}