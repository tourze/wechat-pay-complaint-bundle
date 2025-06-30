<?php

namespace WechatPayComplaintBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Snc\RedisBundle\SncRedisBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\LockServiceBundle\LockServiceBundle;
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
        yield new SncRedisBundle();
        yield new SnowflakeBundle();
        yield new DoctrineSnowflakeBundle();
        yield new DoctrineIndexedBundle();
        yield new DoctrineTimestampBundle();
        yield new LockServiceBundle();
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
            'validation' => [
                'email_validation_mode' => 'html5',
            ],
            'uid' => [
                'default_uuid_version' => 7,
                'time_based_uuid_version' => 7,
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
                    'TestEntities' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/Entity',
                        'prefix' => 'WechatPayComplaintBundle\Tests\Integration\Entity',
                    ],
                ],
            ],
        ]);
        
        // Redis 配置 - 测试环境
        $container->extension('snc_redis', [
            'clients' => [
                'default' => [
                    'type' => 'phpredis',
                    'alias' => 'default',
                    'dsn' => 'redis://127.0.0.1:6379',
                ],
            ],
        ]);

        // Snowflake 配置
        $container->extension('snowflake', [
            'datacenter_id' => 1,
            'worker_id' => 1,
        ]);
        
        // 配置测试服务
        $services = $container->services();
        
        // 模拟 Logger
        $services->set('logger', \Psr\Log\NullLogger::class);
        
        // 模拟 WechatPayBuilder
        $services->set(\WechatPayBundle\Service\WechatPayBuilder::class)
            ->synthetic();
        
        // 模拟 MerchantRepository
        $services->set(\WechatPayBundle\Repository\MerchantRepository::class)
            ->synthetic();
        
        // 模拟 FilesystemOperator
        $services->set(\League\Flysystem\FilesystemOperator::class)
            ->synthetic();
        
        // 模拟 SmartHttpClient
        $services->set(\HttpClientBundle\Service\SmartHttpClient::class)
            ->synthetic();
        
        // 配置实体映射替换 - 使用 ResolveTargetEntityListener
        $services->set('doctrine.orm.listeners.resolve_target_entity')
            ->class(\Doctrine\ORM\Tools\ResolveTargetEntityListener::class)
            ->tag('doctrine.event_listener', ['event' => 'loadClassMetadata']);
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