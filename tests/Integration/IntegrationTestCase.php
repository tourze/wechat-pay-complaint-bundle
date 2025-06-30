<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        self::bootKernel();
        
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        
        $this->setupDatabase();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->entityManager->close();
    }
    
    protected function setupDatabase(): void
    {
        // 配置实体映射替换
        $container = static::getContainer();
        if ($container->has('doctrine.orm.listeners.resolve_target_entity')) {
            $resolveTargetEntityListener = $container->get('doctrine.orm.listeners.resolve_target_entity');
            $resolveTargetEntityListener->addResolveTargetEntity(
                \WechatPayBundle\Entity\Merchant::class,
                \WechatPayComplaintBundle\Tests\Integration\Entity\TestMerchant::class,
                []
            );
        }
        
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        
        // 删除并重新创建所有表
        try {
            $schemaTool->dropSchema($metadata);
        } catch (\Exception $e) {
            // 忽略表不存在的错误
        }
        
        $schemaTool->createSchema($metadata);
    }
    
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }
}