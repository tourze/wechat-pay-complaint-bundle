<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Integration\Repository;

use Doctrine\ORM\EntityManagerInterface;
use WechatPayComplaintBundle\Tests\Integration\IntegrationTestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;
use WechatPayComplaintBundle\Tests\Integration\Entity\TestMerchant;

class ComplaintMediaRepositoryTest extends IntegrationTestCase
{
    private ComplaintMediaRepository $repository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = static::getContainer()->get(ComplaintMediaRepository::class);
    }
    
    public function testFindByMediaUrl(): void
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
        
        // 创建 ComplaintMedia
        $complaintMedia = new ComplaintMedia();
        $complaintMedia->setMediaUrl(['https://example.com/media1.jpg', 'https://example.com/media2.jpg']);
        $complaintMedia->setMediaType('image');
        $complaintMedia->setComplaint($complaint);
        
        $this->entityManager->persist($complaintMedia);
        $this->entityManager->flush();
        
        $foundMedia = $this->repository->findOneBy(['complaint' => $complaint]);
        
        self::assertNotNull($foundMedia);
        self::assertSame(['https://example.com/media1.jpg', 'https://example.com/media2.jpg'], $foundMedia->getMediaUrl());
        self::assertSame('image', $foundMedia->getMediaType());
        
        // 清理测试数据
        $this->entityManager->remove($complaintMedia);
        $this->entityManager->remove($complaint);
        $this->entityManager->remove($merchant);
        $this->entityManager->flush();
    }
}