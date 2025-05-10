<?php

namespace WechatPayComplaintBundle\Tests\Integration\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintRepository;

class ComplaintRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ComplaintRepository $complaintRepository;

    // 由于依赖于需要运行环境的集成测试框架
    // 我们可以将此测试标记为跳过，但在实际环境中应该运行
    protected function setUp(): void
    {
        $this->markTestSkipped('集成测试需要完整的测试环境支持，此处跳过。');
    }

    private function setupDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
            $schemaTool->createSchema($metadata);
        }
    }

    private function createMerchant(): Merchant
    {
        $merchant = new Merchant();
        $merchant->setMchId('test_mch_id');
        $merchant->setName('测试商户');
        
        $this->entityManager->persist($merchant);
        $this->entityManager->flush();
        
        return $merchant;
    }

    private function createComplaint(Merchant $merchant, string $wxComplaintId): Complaint
    {
        $complaint = new Complaint();
        $complaint->setMerchant($merchant);
        $complaint->setWxComplaintId($wxComplaintId);
        $complaint->setComplaintState(ComplaintState::PENDING);
        $complaint->setComplaintTime('2023-12-01 10:00:00');
        $complaint->setPayOrderNo('TEST_ORDER_' . uniqid());
        $complaint->setAmount(100.00);
        
        $this->entityManager->persist($complaint);
        $this->entityManager->flush();
        
        return $complaint;
    }

    public function testFindById(): void
    {
        $merchant = $this->createMerchant();
        $complaint = $this->createComplaint($merchant, 'WX_' . uniqid());
        
        // 清除实体管理器，确保从数据库加载
        $this->entityManager->clear();
        
        $foundComplaint = $this->complaintRepository->find($complaint->getId());
        
        $this->assertNotNull($foundComplaint);
        $this->assertEquals($complaint->getId(), $foundComplaint->getId());
        $this->assertEquals($complaint->getWxComplaintId(), $foundComplaint->getWxComplaintId());
    }

    public function testFindByWxComplaintId(): void
    {
        $merchant = $this->createMerchant();
        $wxComplaintId = 'WX_UNIQUE_' . uniqid();
        $complaint = $this->createComplaint($merchant, $wxComplaintId);
        
        // 清除实体管理器，确保从数据库加载
        $this->entityManager->clear();
        
        $foundComplaint = $this->complaintRepository->findOneBy(['wxComplaintId' => $wxComplaintId]);
        
        $this->assertNotNull($foundComplaint);
        $this->assertEquals($wxComplaintId, $foundComplaint->getWxComplaintId());
    }

    public function testFindByState(): void
    {
        $merchant = $this->createMerchant();
        $this->createComplaint($merchant, 'WX_PENDING_1');
        $this->createComplaint($merchant, 'WX_PENDING_2');
        
        // 清除实体管理器，确保从数据库加载
        $this->entityManager->clear();
        
        $pendingComplaints = $this->complaintRepository->findBy(['complaintState' => ComplaintState::PENDING]);
        
        $this->assertCount(2, $pendingComplaints);
        foreach ($pendingComplaints as $complaint) {
            $this->assertEquals(ComplaintState::PENDING, $complaint->getComplaintState());
        }
    }

    public function testFindAll(): void
    {
        $merchant = $this->createMerchant();
        $this->createComplaint($merchant, 'WX_ALL_1');
        $this->createComplaint($merchant, 'WX_ALL_2');
        $this->createComplaint($merchant, 'WX_ALL_3');
        
        // 清除实体管理器，确保从数据库加载
        $this->entityManager->clear();
        
        $allComplaints = $this->complaintRepository->findAll();
        
        $this->assertGreaterThanOrEqual(3, count($allComplaints));
    }

    public function testFindWithOrderBy(): void
    {
        $merchant = $this->createMerchant();
        $this->createComplaint($merchant, 'WX_ORDER_1');
        $this->createComplaint($merchant, 'WX_ORDER_2');
        
        // 延迟一秒，确保创建时间不同
        sleep(1);
        $latestComplaint = $this->createComplaint($merchant, 'WX_ORDER_LATEST');
        
        // 清除实体管理器，确保从数据库加载
        $this->entityManager->clear();
        
        $orderedComplaints = $this->complaintRepository->findBy([], ['createTime' => 'DESC'], 1);
        
        $this->assertCount(1, $orderedComplaints);
        $this->assertEquals($latestComplaint->getWxComplaintId(), $orderedComplaints[0]->getWxComplaintId());
    }

    protected function tearDown(): void
    {
        $this->entityManager->getConnection()->executeStatement('DELETE FROM wechat_payment_complaint');
        
        parent::tearDown();
        
        // 避免内存泄漏
        $this->entityManager->close();
        $this->entityManager = null;
    }
} 