<?php

namespace WechatPayComplaintBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '消费者投诉')]
class WechatPayComplaintBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \WechatPayBundle\WechatPayBundle::class => ['all' => true],
        ];
    }
}
