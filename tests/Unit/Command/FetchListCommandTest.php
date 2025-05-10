<?php

namespace WechatPayComplaintBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WechatPayBundle\Entity\Merchant;
use WechatPayBundle\Repository\MerchantRepository;
use WechatPayBundle\Service\WechatPayBuilder;
use WechatPayComplaintBundle\Command\FetchListCommand;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;
use WechatPayComplaintBundle\Repository\ComplaintRepository;

class FetchListCommandTest extends TestCase
{
    private FetchListCommand $command;
    private CommandTester $commandTester;
    private LoggerInterface $logger;
    private ComplaintRepository $complaintRepository;
    private ComplaintMediaRepository $mediaRepository;
    private MerchantRepository $merchantRepository;
    private WechatPayBuilder $wechatPayBuilder;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->complaintRepository = $this->createMock(ComplaintRepository::class);
        $this->mediaRepository = $this->createMock(ComplaintMediaRepository::class);
        $this->merchantRepository = $this->createMock(MerchantRepository::class);
        $this->wechatPayBuilder = $this->createMock(WechatPayBuilder::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->command = new FetchListCommand(
            $this->logger,
            $this->complaintRepository,
            $this->mediaRepository,
            $this->merchantRepository,
            $this->wechatPayBuilder,
            $this->entityManager,
        );
        
        $application = new Application();
        $application->add($this->command);
        
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithNoMerchants(): void
    {
        // 设置模拟：没有商户
        $this->merchantRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        // 期望不会请求微信接口
        $this->wechatPayBuilder->expects($this->never())
            ->method('genBuilder');

        // 使用 CommandTester 执行命令
        $this->commandTester->execute([]);
        
        // 成功执行命令应返回 0
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithMerchants(): void
    {
        // 创建模拟商户
        $merchant = $this->createMock(Merchant::class);
        $merchant->method('getMchId')->willReturn('test_mch_id');

        // 设置模拟：有一个商户
        $this->merchantRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$merchant]);

        // 创建部分模拟的命令对象
        $mockCommand = $this->getMockBuilder(FetchListCommand::class)
            ->setConstructorArgs([
                $this->logger,
                $this->complaintRepository,
                $this->mediaRepository,
                $this->merchantRepository,
                $this->wechatPayBuilder,
                $this->entityManager,
            ])
            ->onlyMethods(['request'])
            ->getMock();

        // 设置 request 方法的期望
        $mockCommand->expects($this->once())
            ->method('request')
            ->with(
                $this->identicalTo($merchant),
                $this->equalTo(20),
                $this->equalTo(0),
                $this->isType('string'),
                $this->isType('string')
            );

        // 使用模拟命令创建 CommandTester
        $mockCommandTester = new CommandTester($mockCommand);
        
        // 执行命令
        $mockCommandTester->execute([]);
        
        // 验证执行成功
        $this->assertEquals(0, $mockCommandTester->getStatusCode());
    }

    /**
     * @group slow
     */
    public function testPersistsComplaintData(): void
    {
        // 在测试命令执行时，无法实际执行 request 方法中的 persist 调用
        // 因此这个测试在这里不是真正有效的，完整测试应通过集成测试完成
        $this->markTestSkipped('此测试需要完整的集成测试环境才能有效执行');
    }
} 