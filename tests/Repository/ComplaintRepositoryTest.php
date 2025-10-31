<?php

namespace WechatPayComplaintBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintRepository;

/**
 * @internal
 */
#[CoversClass(ComplaintRepository::class)]
#[RunTestsInSeparateProcesses]
final class ComplaintRepositoryTest extends AbstractRepositoryTestCase
{
    private ComplaintRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ComplaintRepository::class);
    }

    public function testRepositoryService(): void
    {
        $this->assertInstanceOf(ComplaintRepository::class, $this->repository);
    }

    public function testRepositoryCanBeRetrieved(): void
    {
        $repository = self::getService(ComplaintRepository::class);
        $this->assertInstanceOf(ComplaintRepository::class, $repository);
    }

    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        $complaint = $this->createComplaint();

        $this->repository->save($complaint, false);

        // 检查实体管理器中的单位工作是否包含该实体
        $unitOfWork = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($unitOfWork->isScheduledForInsert($complaint));

        // 手动flush后单位工作应该清空，实体应该被持久化
        self::getEntityManager()->flush();
        $this->assertFalse($unitOfWork->isScheduledForInsert($complaint));

        $found = $this->repository->find($complaint->getId());
        $this->assertInstanceOf(Complaint::class, $found);
    }

    public function testRemoveWithFlushShouldDeleteEntity(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);
        $id = $complaint->getId();

        $this->repository->remove($complaint, true);

        $this->assertEntityNotExists(Complaint::class, $id);
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testRemoveWithoutFlushShouldNotDeleteImmediately(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);
        $id = $complaint->getId();

        $this->repository->remove($complaint, false);

        // 检查实体管理器中的单位工作是否包含该实体
        $unitOfWork = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($unitOfWork->isScheduledForDelete($complaint));

        // 手动flush后单位工作应该清空，实体应该被删除
        self::getEntityManager()->flush();
        $this->assertFalse($unitOfWork->isScheduledForDelete($complaint));

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByMerchant(): void
    {
        $merchant = $this->createMerchant();
        $complaint1 = $this->createComplaintWithMerchant($merchant);
        $complaint2 = $this->createComplaintWithMerchant($merchant);
        $otherComplaint = $this->createComplaint();

        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);
        $this->persistAndFlush($otherComplaint);

        $result = $this->repository->findBy(['merchant' => $merchant]);
        $this->assertCount(2, $result);
        $this->assertContains($complaint1, $result);
        $this->assertContains($complaint2, $result);
    }

    public function testCountByComplaintState(): void
    {
        $pendingComplaint = $this->createComplaint('', ComplaintState::PENDING);
        $processingComplaint = $this->createComplaint('', ComplaintState::PROCESSING);
        $processedComplaint = $this->createComplaint('', ComplaintState::PROCESSED);

        $this->persistAndFlush($pendingComplaint);
        $this->persistAndFlush($processingComplaint);
        $this->persistAndFlush($processedComplaint);

        $pendingCount = $this->repository->count(['complaintState' => ComplaintState::PENDING]);
        $processingCount = $this->repository->count(['complaintState' => ComplaintState::PROCESSING]);
        $processedCount = $this->repository->count(['complaintState' => ComplaintState::PROCESSED]);

        $this->assertGreaterThanOrEqual(1, $pendingCount);
        $this->assertGreaterThanOrEqual(1, $processingCount);
        $this->assertGreaterThanOrEqual(1, $processedCount);
    }

    public function testFindByPayerPhoneIsNull(): void
    {
        $complaintWithPhone = $this->createComplaint();
        $complaintWithPhone->setPayerPhone('13800138000');

        $complaintWithoutPhone = $this->createComplaint();
        $complaintWithoutPhone->setPayerPhone(null);

        $this->persistAndFlush($complaintWithPhone);
        $this->persistAndFlush($complaintWithoutPhone);

        $result = $this->repository->findBy(['payerPhone' => null]);
        $this->assertContains($complaintWithoutPhone, $result);
    }

    public function testFindByWxPayOrderNoIsNull(): void
    {
        $complaintWithWxOrder = $this->createComplaint();
        $complaintWithWxOrder->setWxPayOrderNo('wx_order_001');

        $complaintWithoutWxOrder = $this->createComplaint();
        $complaintWithoutWxOrder->setWxPayOrderNo(null);

        $this->persistAndFlush($complaintWithWxOrder);
        $this->persistAndFlush($complaintWithoutWxOrder);

        $result = $this->repository->findBy(['wxPayOrderNo' => null]);
        $this->assertContains($complaintWithoutWxOrder, $result);
    }

    public function testFindByApplyRefundAmountIsNull(): void
    {
        $complaintWithRefund = $this->createComplaint();
        $complaintWithRefund->setApplyRefundAmount(50.0);

        $complaintWithoutRefund = $this->createComplaint();
        $complaintWithoutRefund->setApplyRefundAmount(null);

        $this->persistAndFlush($complaintWithRefund);
        $this->persistAndFlush($complaintWithoutRefund);

        $result = $this->repository->findBy(['applyRefundAmount' => null]);
        $this->assertContains($complaintWithoutRefund, $result);
    }

    public function testCountByPayerPhoneIsNull(): void
    {
        $complaintWithPhone = $this->createComplaint();
        $complaintWithPhone->setPayerPhone('13800138000');

        $complaintWithoutPhone = $this->createComplaint();
        $complaintWithoutPhone->setPayerPhone(null);

        $this->persistAndFlush($complaintWithPhone);
        $this->persistAndFlush($complaintWithoutPhone);

        $count = $this->repository->count(['payerPhone' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByComplaintFullRefundedIsNull(): void
    {
        $complaintRefunded = $this->createComplaint();
        $complaintRefunded->setComplaintFullRefunded(true);

        $complaintUnknown = $this->createComplaint();
        $complaintUnknown->setComplaintFullRefunded(null);

        $this->persistAndFlush($complaintRefunded);
        $this->persistAndFlush($complaintUnknown);

        $count = $this->repository->count(['complaintFullRefunded' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $merchant = $this->createMerchant();

        $oldComplaint = $this->createComplaintWithMerchant($merchant);
        $oldComplaint->setComplaintTime('2024-01-01 10:00:00');

        $newComplaint = $this->createComplaintWithMerchant($merchant);
        $newComplaint->setComplaintTime('2024-01-02 10:00:00');

        $this->persistAndFlush($oldComplaint);
        $this->persistAndFlush($newComplaint);

        $result = $this->repository->findOneBy(
            ['merchant' => $merchant],
            ['complaintTime' => 'DESC']
        );
        $this->assertSame($newComplaint, $result);

        $result = $this->repository->findOneBy(
            ['merchant' => $merchant],
            ['complaintTime' => 'ASC']
        );
        $this->assertSame($oldComplaint, $result);
    }

    private function createMerchant(): Merchant
    {
        $merchant = new Merchant();
        $merchant->setMchId('MERCHANT_' . uniqid());
        $merchant->setApiKey('test_api_key');
        $merchant->setCertSerial('test_cert_serial');
        $this->persistAndFlush($merchant);

        return $merchant;
    }

    private function createComplaintWithMerchant(Merchant $merchant, string $wxComplaintId = '', ComplaintState $state = ComplaintState::PENDING): Complaint
    {
        if ('' === $wxComplaintId) {
            $wxComplaintId = 'WX' . uniqid();
        }

        $complaint = new Complaint();
        $complaint->setMerchant($merchant);
        $complaint->setWxComplaintId($wxComplaintId);
        $complaint->setComplaintTime('2023-01-01 12:00:00');
        $complaint->setComplaintState($state);
        $complaint->setPayOrderNo('ORDER-' . uniqid());
        $complaint->setAmount(100.00);

        return $complaint;
    }

    private function createComplaint(string $wxComplaintId = '', ComplaintState $state = ComplaintState::PENDING): Complaint
    {
        if ('' === $wxComplaintId) {
            $wxComplaintId = 'WX' . uniqid();
        }

        $merchant = new Merchant();
        $merchant->setMchId('MERCHANT_' . uniqid());
        $merchant->setApiKey('test_api_key');
        $merchant->setCertSerial('test_cert_serial');
        $this->persistAndFlush($merchant);

        $complaint = new Complaint();
        $complaint->setMerchant($merchant);
        $complaint->setWxComplaintId($wxComplaintId);
        $complaint->setComplaintTime('2023-01-01 12:00:00');
        $complaint->setComplaintState($state);
        $complaint->setPayOrderNo('ORDER-' . uniqid());
        $complaint->setAmount(100.00);

        return $complaint;
    }

    public function testFindByMerchantAssociation(): void
    {
        $merchant1 = $this->createMerchant();
        $merchant2 = $this->createMerchant();

        $complaint1 = $this->createComplaintWithMerchant($merchant1);
        $complaint2 = $this->createComplaintWithMerchant($merchant2);

        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $results = $this->repository->findBy(['merchant' => $merchant1]);
        $this->assertCount(1, $results);
        $this->assertContains($complaint1, $results);
        $this->assertNotContains($complaint2, $results);
    }

    public function testCountByMerchantAssociation(): void
    {
        $merchant = $this->createMerchant();

        $complaint1 = $this->createComplaintWithMerchant($merchant);
        $complaint2 = $this->createComplaintWithMerchant($merchant);

        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $count = $this->repository->count(['merchant' => $merchant]);
        $this->assertEquals(2, $count);
    }

    public function testFindOneByMerchantWithOrderBy(): void
    {
        $merchant = $this->createMerchant();

        $complaint1 = $this->createComplaintWithMerchant($merchant);
        $complaint1->setWxComplaintId('A_' . uniqid());

        $complaint2 = $this->createComplaintWithMerchant($merchant);
        $complaint2->setWxComplaintId('Z_' . uniqid());

        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $resultAsc = $this->repository->findOneBy(
            ['merchant' => $merchant],
            ['wxComplaintId' => 'ASC']
        );
        $this->assertNotNull($resultAsc);
        $wxComplaintId = $resultAsc->getWxComplaintId();
        $this->assertNotNull($wxComplaintId);
        $this->assertStringStartsWith('A_', $wxComplaintId);

        $resultDesc = $this->repository->findOneBy(
            ['merchant' => $merchant],
            ['wxComplaintId' => 'DESC']
        );
        $this->assertNotNull($resultDesc);
        $wxComplaintId = $resultDesc->getWxComplaintId();
        $this->assertNotNull($wxComplaintId);
        $this->assertStringStartsWith('Z_', $wxComplaintId);
    }

    public function testCountByWxPayOrderNoIsNull(): void
    {
        $complaintWithWxOrder = $this->createComplaint();
        $complaintWithWxOrder->setWxPayOrderNo('wx_order_001');

        $complaintWithoutWxOrder = $this->createComplaint();
        $complaintWithoutWxOrder->setWxPayOrderNo(null);

        $this->persistAndFlush($complaintWithWxOrder);
        $this->persistAndFlush($complaintWithoutWxOrder);

        $count = $this->repository->count(['wxPayOrderNo' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByApplyRefundAmountIsNull(): void
    {
        $complaintWithRefund = $this->createComplaint();
        $complaintWithRefund->setApplyRefundAmount(100.0);

        $complaintWithoutRefund = $this->createComplaint();
        $complaintWithoutRefund->setApplyRefundAmount(null);

        $this->persistAndFlush($complaintWithRefund);
        $this->persistAndFlush($complaintWithoutRefund);

        $count = $this->repository->count(['applyRefundAmount' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationMerchantShouldReturnCorrectNumber(): void
    {
        $merchant = $this->createMerchant();
        $otherMerchant = $this->createMerchant();

        // 为目标商户创建4个投诉
        for ($i = 0; $i < 4; ++$i) {
            $complaint = $this->createComplaintWithMerchant($merchant);
            $this->persistAndFlush($complaint);
        }

        // 为其他商户创建2个投诉
        for ($i = 0; $i < 2; ++$i) {
            $complaint = $this->createComplaintWithMerchant($otherMerchant);
            $this->persistAndFlush($complaint);
        }

        $count = $this->repository->count(['merchant' => $merchant]);
        $this->assertSame(4, $count);
    }

    public function testFindOneByAssociationMerchantShouldReturnMatchingEntity(): void
    {
        $merchant = $this->createMerchant();
        $otherMerchant = $this->createMerchant();

        $targetComplaint = $this->createComplaintWithMerchant($merchant);
        $otherComplaint = $this->createComplaintWithMerchant($otherMerchant);

        $this->persistAndFlush($targetComplaint);
        $this->persistAndFlush($otherComplaint);

        $result = $this->repository->findOneBy(['merchant' => $merchant]);
        $this->assertNotNull($result);
        $this->assertSame($targetComplaint, $result);
        $this->assertSame($merchant, $result->getMerchant());
    }

    protected function createNewEntity(): object
    {
        return $this->createComplaint();
    }

    protected function getRepository(): ComplaintRepository
    {
        return $this->repository;
    }
}
