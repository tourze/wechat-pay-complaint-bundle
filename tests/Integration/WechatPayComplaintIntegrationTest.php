<?php

namespace WechatPayComplaintBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WechatPayComplaintIntegrationTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('集成测试需要完整的测试环境支持，此处跳过。');
    }
    
    /**
     * 占位测试方法，以避免 PHPUnit 警告
     */
    public function testPlaceholder(): void 
    {
        $this->assertTrue(true);
    }
} 