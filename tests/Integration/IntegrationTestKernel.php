<?php

namespace WechatPayComplaintBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\SnowflakeBundle\SnowflakeBundle;
use Tourze\Symfony\CronJob\CronJobBundle;
use WechatPayBundle\WechatPayBundle;
use WechatPayComplaintBundle\WechatPayComplaintBundle;

class IntegrationTestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new SnowflakeBundle();
        yield new DoctrineSnowflakeBundle();
        yield new DoctrineIndexedBundle();
        yield new DoctrineTimestampBundle();
        yield new CronJobBundle();
        yield new WechatPayBundle();
        yield new WechatPayComplaintBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // 基本框架配置
        $container->extension('framework', [
            'secret' => 'TEST_SECRET',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => [
                'log' => true,
            ],
        ]);

        // Doctrine 配置 - 使用内存数据库
        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'controller_resolver' => [
                    'auto_mapping' => false,
                ],
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping' => true,
                'mappings' => [
                    'WechatPayComplaintEntities' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/../../src/Entity',
                        'prefix' => 'WechatPayComplaintBundle\Entity',
                    ],
                    'WechatPayEntities' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/../../../wechat-pay-bundle/src/Entity',
                        'prefix' => 'WechatPayBundle\Entity',
                    ],
                    'TestEntities' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/Entity',
                        'prefix' => 'WechatPayComplaintBundle\Tests\Integration\Entity',
                    ],
                ],
            ],
        ]);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }
} 