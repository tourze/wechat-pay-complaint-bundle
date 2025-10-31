<?php

namespace WechatPayComplaintBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use WechatPayBundle\WechatPayBundle;

class WechatPayComplaintBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            WechatPayBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
        ];
    }
}
