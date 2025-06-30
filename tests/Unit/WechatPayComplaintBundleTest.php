<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WechatPayComplaintBundle\WechatPayComplaintBundle;

class WechatPayComplaintBundleTest extends TestCase
{
    public function testBundleInstantiation(): void
    {
        $bundle = new WechatPayComplaintBundle();
        
        self::assertInstanceOf(WechatPayComplaintBundle::class, $bundle);
    }
}