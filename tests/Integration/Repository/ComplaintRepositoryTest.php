<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Integration\Repository;

use Doctrine\ORM\EntityManagerInterface;
use WechatPayComplaintBundle\Tests\Integration\IntegrationTestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintRepository;
use WechatPayComplaintBundle\Tests\Integration\Entity\TestMerchant;

class ComplaintRepositoryTest extends IntegrationTestCase
{
    private ComplaintRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = static::getContainer()->get(ComplaintRepository::class);
    }
    
    public function testFindByWxComplaintId(): void
    {
        // 创建测试商户
        $merchant = new TestMerchant();
        $merchant->setMchId('test_mch_id');
        $merchant->setName('Test Merchant');
        
        $this->entityManager->persist($merchant);
        $this->entityManager->flush();
        
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
        
        $foundComplaint = $this->repository->findOneBy(['wxComplaintId' => 'test_complaint_id']);
        
        self::assertNotNull($foundComplaint);
        self::assertSame('test_complaint_id', $foundComplaint->getWxComplaintId());
        
        // 清理测试数据
        $this->entityManager->remove($complaint);
        $this->entityManager->remove($merchant);
        $this->entityManager->flush();
    }
}