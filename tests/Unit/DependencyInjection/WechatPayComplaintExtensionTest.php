<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatPayComplaintBundle\DependencyInjection\WechatPayComplaintExtension;

class WechatPayComplaintExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new WechatPayComplaintExtension();
        
        $extension->load([], $container);
        
        // 验证服务是否被正确加载
        self::assertTrue($container->hasDefinition('WechatPayComplaintBundle\Repository\ComplaintRepository'));
        self::assertTrue($container->hasDefinition('WechatPayComplaintBundle\Repository\ComplaintReplyRepository'));
        self::assertTrue($container->hasDefinition('WechatPayComplaintBundle\Repository\ComplaintMediaRepository'));
        self::assertTrue($container->hasDefinition('WechatPayComplaintBundle\Command\FetchListCommand'));
    }
}