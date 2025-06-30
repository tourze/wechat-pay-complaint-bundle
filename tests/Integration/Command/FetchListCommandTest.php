<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use WechatPayComplaintBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use WechatPayBundle\Repository\MerchantRepository;
use WechatPayBundle\Service\WechatPayBuilder;
use WechatPayComplaintBundle\Command\FetchListCommand;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;
use WechatPayComplaintBundle\Repository\ComplaintRepository;

class FetchListCommandTest extends IntegrationTestCase
{
    private CommandTester $commandTester;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建 mock 对象
        $logger = $this->createMock(LoggerInterface::class);
        $complaintRepository = $this->createMock(ComplaintRepository::class);
        $mediaRepository = $this->createMock(ComplaintMediaRepository::class);
        $merchantRepository = $this->createMock(MerchantRepository::class);
        $wechatPayBuilder = $this->createMock(WechatPayBuilder::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        // 设置 merchantRepository 返回空数组
        $merchantRepository->method('findAll')->willReturn([]);
        
        $command = new FetchListCommand(
            $logger,
            $complaintRepository,
            $mediaRepository,
            $merchantRepository,
            $wechatPayBuilder,
            $entityManager
        );
        
        $application = new Application();
        $application->add($command);
        
        $this->commandTester = new CommandTester($command);
    }
    
    public function testExecuteSuccess(): void
    {
        $this->commandTester->execute([]);
        
        self::assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}