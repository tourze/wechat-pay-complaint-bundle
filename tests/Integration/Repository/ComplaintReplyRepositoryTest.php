<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Integration\Repository;

use Doctrine\ORM\EntityManagerInterface;
use WechatPayComplaintBundle\Tests\Integration\IntegrationTestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintReplyRepository;
use WechatPayComplaintBundle\Tests\Integration\Entity\TestMerchant;

class ComplaintReplyRepositoryTest extends IntegrationTestCase
{
    private ComplaintReplyRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = static::getContainer()->get(ComplaintReplyRepository::class);
    }
    
    public function testFindByComplaint(): void
    {
        // 创建测试商户
        $merchant = new TestMerchant();
        $merchant->setMchId('test_mch_id');
        $merchant->setName('Test Merchant');
        
        $this->entityManager->persist($merchant);
        $this->entityManager->flush();
        
        // 首先创建一个 Complaint 实体
        $complaint = new Complaint();
        $complaint->setMerchant($merchant);
        $complaint->setWxComplaintId('test_complaint_id');
        $complaint->setComplaintTime('2025-01-01 12:00:00');
        $complaint->setComplaintDetail('Test complaint detail');
        $complaint->setComplaintState(ComplaintState::PENDING);
        $complaint->setPayerPhone('13800138000');
        $complaint->setPayOrderNo('PAY123456');
        $complaint->setAmount(100.00);
        
        $this->entityManager->persist($complaint);
        $this->entityManager->flush();
        
        // 创建 ComplaintReply
        $complaintReply = new ComplaintReply();
        $complaintReply->setComplaint($complaint);
        $complaintReply->setContent('Test response content');
        
        $this->entityManager->persist($complaintReply);
        $this->entityManager->flush();
        
        $foundReply = $this->repository->findOneBy(['complaint' => $complaint]);
        
        self::assertNotNull($foundReply);
        self::assertSame($complaint, $foundReply->getComplaint());
        self::assertSame('Test response content', $foundReply->getContent());
        
        // 清理测试数据
        $this->entityManager->remove($complaintReply);
        $this->entityManager->remove($complaint);
        $this->entityManager->remove($merchant);
        $this->entityManager->flush();
    }
}