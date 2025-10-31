<?php

namespace WechatPayComplaintBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintReplyRepository;

/**
 * @internal
 */
#[CoversClass(ComplaintReplyRepository::class)]
#[RunTestsInSeparateProcesses]
final class ComplaintReplyRepositoryTest extends AbstractRepositoryTestCase
{
    private ComplaintReplyRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ComplaintReplyRepository::class);
    }

    public function testRepositoryService(): void
    {
        $this->assertInstanceOf(ComplaintReplyRepository::class, $this->repository);
    }

    public function testRepositoryCanBeRetrieved(): void
    {
        $repository = self::getService(ComplaintReplyRepository::class);
        $this->assertInstanceOf(ComplaintReplyRepository::class, $repository);
    }

    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        $complaintReply = $this->createComplaintReply();

        $this->repository->save($complaintReply, false);

        // 检查实体管理器中的单位工作是否包含该实体
        $unitOfWork = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($unitOfWork->isScheduledForInsert($complaintReply));

        // 手动flush后单位工作应该清空，实体应该被持久化
        self::getEntityManager()->flush();
        $this->assertFalse($unitOfWork->isScheduledForInsert($complaintReply));

        $found = $this->repository->find($complaintReply->getId());
        $this->assertInstanceOf(ComplaintReply::class, $found);
    }

    public function testRemoveWithFlushShouldDeleteEntity(): void
    {
        $complaintReply = $this->createComplaintReply();
        $this->persistAndFlush($complaintReply);
        $id = $complaintReply->getId();

        $this->repository->remove($complaintReply, true);

        $this->assertEntityNotExists(ComplaintReply::class, $id);
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testRemoveWithoutFlushShouldNotDeleteImmediately(): void
    {
        $complaintReply = $this->createComplaintReply();
        $this->persistAndFlush($complaintReply);
        $id = $complaintReply->getId();

        $this->repository->remove($complaintReply, false);

        // 检查实体管理器中的单位工作是否包含该实体
        $unitOfWork = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($unitOfWork->isScheduledForDelete($complaintReply));

        // 手动flush后单位工作应该清空，实体应该被删除
        self::getEntityManager()->flush();
        $this->assertFalse($unitOfWork->isScheduledForDelete($complaintReply));

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    private function createComplaint(): Complaint
    {
        $merchant = new Merchant();
        $merchant->setMchId('MERCHANT_' . uniqid());
        $merchant->setApiKey('test_api_key');
        $merchant->setCertSerial('test_cert_serial');
        $this->persistAndFlush($merchant);

        $complaint = new Complaint();
        $complaint->setMerchant($merchant);
        $complaint->setWxComplaintId('WX' . uniqid());
        $complaint->setComplaintTime('2023-01-01 12:00:00');
        $complaint->setComplaintState(ComplaintState::PENDING);
        $complaint->setPayOrderNo('ORDER-' . uniqid());
        $complaint->setAmount(100.00);

        return $complaint;
    }

    public function testFindByComplaint(): void
    {
        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $reply1 = $this->createComplaintReply('回复内容1', $complaint1);
        $reply2 = $this->createComplaintReply('回复内容2', $complaint1);
        $reply3 = $this->createComplaintReply('回复内容3', $complaint2);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);
        $this->persistAndFlush($reply3);

        $result = $this->repository->findBy(['complaint' => $complaint1]);
        $this->assertCount(2, $result);
        $this->assertContains($reply1, $result);
        $this->assertContains($reply2, $result);
    }

    public function testCountByComplaint(): void
    {
        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $reply1 = $this->createComplaintReply('回复1', $complaint1);
        $reply2 = $this->createComplaintReply('回复2', $complaint1);
        $reply3 = $this->createComplaintReply('回复3', $complaint2);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);
        $this->persistAndFlush($reply3);

        $count1 = $this->repository->count(['complaint' => $complaint1]);
        $count2 = $this->repository->count(['complaint' => $complaint2]);

        $this->assertEquals(2, $count1);
        $this->assertEquals(1, $count2);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $reply1 = $this->createComplaintReply('A回复', $complaint);
        $reply2 = $this->createComplaintReply('B回复', $complaint);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);

        $result = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['content' => 'DESC']
        );
        $this->assertNotNull($result);
        $this->assertEquals('B回复', $result->getContent());

        $result = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['content' => 'ASC']
        );
        $this->assertNotNull($result);
        $this->assertEquals('A回复', $result->getContent());
    }

    private function createComplaintReply(string $content = '默认回复内容', ?Complaint $complaint = null): ComplaintReply
    {
        if (null === $complaint) {
            $complaint = $this->createComplaint();
            $this->persistAndFlush($complaint);
        }

        $complaintReply = new ComplaintReply();
        $complaintReply->setContent($content);
        $complaintReply->setComplaint($complaint);

        return $complaintReply;
    }

    public function testFindOneByComplaintWithOrderBy(): void
    {
        $this->clearAllData();

        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $replyA = $this->createComplaintReply('A_内容', $complaint);
        $replyZ = $this->createComplaintReply('Z_内容', $complaint);

        $this->persistAndFlush($replyA);
        $this->persistAndFlush($replyZ);

        $resultAsc = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['content' => 'ASC']
        );
        $this->assertNotNull($resultAsc);
        $this->assertEquals('A_内容', $resultAsc->getContent());

        $resultDesc = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['content' => 'DESC']
        );
        $this->assertNotNull($resultDesc);
        $this->assertEquals('Z_内容', $resultDesc->getContent());
    }

    private function clearAllData(): void
    {
        $entityManager = self::getEntityManager();
        $existingEntities = $this->repository->findAll();
        foreach ($existingEntities as $entity) {
            $entityManager->remove($entity);
        }
        $entityManager->flush();
    }

    public function testFindByComplaintAssociation(): void
    {
        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $reply1 = $this->createComplaintReply('回复1', $complaint1);
        $reply2 = $this->createComplaintReply('回复2', $complaint2);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);

        $results = $this->repository->findBy(['complaint' => $complaint1]);
        $this->assertCount(1, $results);
        $this->assertContains($reply1, $results);
        $this->assertNotContains($reply2, $results);
    }

    public function testCountByComplaintAssociation(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $reply1 = $this->createComplaintReply('回复1', $complaint);
        $reply2 = $this->createComplaintReply('回复2', $complaint);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);

        $count = $this->repository->count(['complaint' => $complaint]);
        $this->assertEquals(2, $count);
    }

    protected function createNewEntity(): object
    {
        return $this->createComplaintReply();
    }

    protected function getRepository(): ComplaintReplyRepository
    {
        return $this->repository;
    }

    public function testFindByCreateTimeIsNull(): void
    {
        $this->clearAllData();

        $complaintReply = $this->createComplaintReply('测试回复');
        $this->persistAndFlush($complaintReply);

        // 使用数据库直接设置为null来绕过自动时间戳
        self::getEntityManager()->getConnection()->executeStatement(
            'UPDATE wechat_payment_complaint_reply SET create_time = NULL WHERE id = ?',
            [$complaintReply->getId()]
        );
        self::getEntityManager()->clear();

        $results = $this->repository->findBy(['createTime' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $complaintReply->getId()) {
                $this->assertNull($result->getCreateTime());
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '未找到createTime为null的记录');
    }

    public function testCountByCreateTimeIsNull(): void
    {
        $this->clearAllData();

        $complaintReply = $this->createComplaintReply('测试回复');
        $this->persistAndFlush($complaintReply);

        // 使用数据库直接设置为null来绕过自动时间戳
        self::getEntityManager()->getConnection()->executeStatement(
            'UPDATE wechat_payment_complaint_reply SET create_time = NULL WHERE id = ?',
            [$complaintReply->getId()]
        );
        self::getEntityManager()->clear();

        $count = $this->repository->count(['createTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByUpdateTimeIsNull(): void
    {
        $this->clearAllData();

        $complaintReply = $this->createComplaintReply('测试回复');
        $this->persistAndFlush($complaintReply);

        // 使用数据库直接设置为null来绕过自动时间戳
        self::getEntityManager()->getConnection()->executeStatement(
            'UPDATE wechat_payment_complaint_reply SET update_time = NULL WHERE id = ?',
            [$complaintReply->getId()]
        );
        self::getEntityManager()->clear();

        $results = $this->repository->findBy(['updateTime' => null]);
        $this->assertGreaterThanOrEqual(1, count($results));

        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $complaintReply->getId()) {
                $this->assertNull($result->getUpdateTime());
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, '未找到updateTime为null的记录');
    }

    public function testCountByUpdateTimeIsNull(): void
    {
        $this->clearAllData();

        $complaintReply = $this->createComplaintReply('测试回复');
        $this->persistAndFlush($complaintReply);

        // 使用数据库直接设置为null来绕过自动时间戳
        self::getEntityManager()->getConnection()->executeStatement(
            'UPDATE wechat_payment_complaint_reply SET update_time = NULL WHERE id = ?',
            [$complaintReply->getId()]
        );
        self::getEntityManager()->clear();

        $count = $this->repository->count(['updateTime' => null]);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByContentWithOrderBy(): void
    {
        $this->clearAllData();

        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $replyA = $this->createComplaintReply('A_回复内容', $complaint);
        $replyZ = $this->createComplaintReply('Z_回复内容', $complaint);

        $this->persistAndFlush($replyA);
        $this->persistAndFlush($replyZ);

        $resultAsc = $this->repository->findOneBy(
            [],
            ['content' => 'ASC']
        );
        $this->assertNotNull($resultAsc);
        $this->assertEquals('A_回复内容', $resultAsc->getContent());

        $resultDesc = $this->repository->findOneBy(
            [],
            ['content' => 'DESC']
        );
        $this->assertNotNull($resultDesc);
        $this->assertEquals('Z_回复内容', $resultDesc->getContent());
    }

    public function testFindOneByIdWithOrderBy(): void
    {
        $this->clearAllData();

        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $reply1 = $this->createComplaintReply('回复1', $complaint);
        $reply2 = $this->createComplaintReply('回复2', $complaint);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);

        $resultAsc = $this->repository->findOneBy(
            [],
            ['id' => 'ASC']
        );
        $this->assertNotNull($resultAsc);

        $resultDesc = $this->repository->findOneBy(
            [],
            ['id' => 'DESC']
        );
        $this->assertNotNull($resultDesc);
        $this->assertNotEquals($resultAsc->getId(), $resultDesc->getId());
    }

    public function testFindOneByCreateTimeWithOrderBy(): void
    {
        $this->clearAllData();

        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $reply1 = $this->createComplaintReply('回复1', $complaint);
        $reply1->setCreateTime(new \DateTimeImmutable('2023-01-01'));
        $this->persistAndFlush($reply1);

        $reply2 = $this->createComplaintReply('回复2', $complaint);
        $reply2->setCreateTime(new \DateTimeImmutable('2023-12-31'));
        $this->persistAndFlush($reply2);

        $resultAsc = $this->repository->findOneBy(
            [],
            ['createTime' => 'ASC']
        );
        $this->assertNotNull($resultAsc);
        $this->assertEquals('回复1', $resultAsc->getContent());

        $resultDesc = $this->repository->findOneBy(
            [],
            ['createTime' => 'DESC']
        );
        $this->assertNotNull($resultDesc);
        $this->assertEquals('回复2', $resultDesc->getContent());
    }

    public function testFindOneByUpdateTimeWithOrderBy(): void
    {
        $this->clearAllData();

        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $reply1 = $this->createComplaintReply('回复1', $complaint);
        $reply1->setUpdateTime(new \DateTimeImmutable('2023-01-01'));
        $this->persistAndFlush($reply1);

        $reply2 = $this->createComplaintReply('回复2', $complaint);
        $reply2->setUpdateTime(new \DateTimeImmutable('2023-12-31'));
        $this->persistAndFlush($reply2);

        $resultAsc = $this->repository->findOneBy(
            [],
            ['updateTime' => 'ASC']
        );
        $this->assertNotNull($resultAsc);
        $this->assertEquals('回复1', $resultAsc->getContent());

        $resultDesc = $this->repository->findOneBy(
            [],
            ['updateTime' => 'DESC']
        );
        $this->assertNotNull($resultDesc);
        $this->assertEquals('回复2', $resultDesc->getContent());
    }

    public function testFindOneByAssociationComplaintShouldReturnMatchingEntity(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $complaintReply = $this->createComplaintReply('回复内容', $complaint);
        $this->persistAndFlush($complaintReply);

        $result = $this->repository->findOneBy(['complaint' => $complaint]);
        $this->assertNotNull($result);
        $this->assertEquals($complaint->getId(), $result->getComplaint()?->getId());
    }

    public function testCountByAssociationComplaintShouldReturnCorrectNumber(): void
    {
        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $reply1 = $this->createComplaintReply('回复1', $complaint1);
        $reply2 = $this->createComplaintReply('回复2', $complaint1);
        $reply3 = $this->createComplaintReply('回复3', $complaint2);

        $this->persistAndFlush($reply1);
        $this->persistAndFlush($reply2);
        $this->persistAndFlush($reply3);

        $count1 = $this->repository->count(['complaint' => $complaint1]);
        $count2 = $this->repository->count(['complaint' => $complaint2]);

        $this->assertEquals(2, $count1);
        $this->assertEquals(1, $count2);
    }
}
