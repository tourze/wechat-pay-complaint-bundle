<?php

namespace WechatPayComplaintBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;

/**
 * @internal
 */
#[CoversClass(ComplaintMediaRepository::class)]
#[RunTestsInSeparateProcesses]
final class ComplaintMediaRepositoryTest extends AbstractRepositoryTestCase
{
    private ComplaintMediaRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ComplaintMediaRepository::class);
    }

    public function testRepositoryService(): void
    {
        $this->assertInstanceOf(ComplaintMediaRepository::class, $this->repository);
    }

    public function testRepositoryCanBeRetrieved(): void
    {
        $repository = self::getService(ComplaintMediaRepository::class);
        $this->assertInstanceOf(ComplaintMediaRepository::class, $repository);
    }

    public function testSaveWithoutFlushShouldNotPersistImmediately(): void
    {
        $this->clearAllData();

        $complaintMedia = $this->createComplaintMedia();

        $this->repository->save($complaintMedia, false);

        // 检查实体管理器中的单位工作是否包含该实体
        $unitOfWork = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($unitOfWork->isScheduledForInsert($complaintMedia));

        // 手动flush后单位工作应该清空，实体应该被持久化
        self::getEntityManager()->flush();
        $this->assertFalse($unitOfWork->isScheduledForInsert($complaintMedia));

        $found = $this->repository->find($complaintMedia->getId());
        $this->assertInstanceOf(ComplaintMedia::class, $found);
    }

    public function testRemoveWithFlushShouldDeleteEntity(): void
    {
        $complaintMedia = $this->createComplaintMedia();
        $this->persistAndFlush($complaintMedia);
        $id = $complaintMedia->getId();

        $this->repository->remove($complaintMedia, true);

        $this->assertEntityNotExists(ComplaintMedia::class, $id);
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testRemoveWithoutFlushShouldNotDeleteImmediately(): void
    {
        $this->clearAllData();

        $complaintMedia = $this->createComplaintMedia();
        $this->persistAndFlush($complaintMedia);
        $id = $complaintMedia->getId();

        $this->repository->remove($complaintMedia, false);

        // 检查实体管理器中的单位工作是否包含该实体
        $unitOfWork = self::getEntityManager()->getUnitOfWork();
        $this->assertTrue($unitOfWork->isScheduledForDelete($complaintMedia));

        // 手动flush后单位工作应该清空，实体应该被删除
        self::getEntityManager()->flush();
        $this->assertFalse($unitOfWork->isScheduledForDelete($complaintMedia));

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

        $media1 = $this->createComplaintMedia('image', $complaint1);
        $media2 = $this->createComplaintMedia('video', $complaint1);
        $media3 = $this->createComplaintMedia('document', $complaint2);

        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);
        $this->persistAndFlush($media3);

        $result = $this->repository->findBy(['complaint' => $complaint1]);
        $this->assertCount(2, $result);
        $this->assertContains($media1, $result);
        $this->assertContains($media2, $result);
    }

    public function testFindByMediaType(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $imageMedia = $this->createComplaintMedia('image', $complaint);
        $videoMedia = $this->createComplaintMedia('video', $complaint);

        $this->persistAndFlush($imageMedia);
        $this->persistAndFlush($videoMedia);

        $imageResult = $this->repository->findBy(['mediaType' => 'image']);
        $videoResult = $this->repository->findBy(['mediaType' => 'video']);

        $this->assertContains($imageMedia, $imageResult);
        $this->assertContains($videoMedia, $videoResult);
    }

    public function testCountByComplaint(): void
    {
        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $media1 = $this->createComplaintMedia('image', $complaint1);
        $media2 = $this->createComplaintMedia('video', $complaint1);
        $media3 = $this->createComplaintMedia('document', $complaint2);

        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);
        $this->persistAndFlush($media3);

        $count1 = $this->repository->count(['complaint' => $complaint1]);
        $count2 = $this->repository->count(['complaint' => $complaint2]);

        $this->assertEquals(2, $count1);
        $this->assertEquals(1, $count2);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $imageMedia = $this->createComplaintMedia('image', $complaint);
        $videoMedia = $this->createComplaintMedia('video', $complaint);

        $this->persistAndFlush($imageMedia);
        $this->persistAndFlush($videoMedia);

        $result = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['mediaType' => 'ASC']
        );
        $this->assertInstanceOf(ComplaintMedia::class, $result);
        $this->assertEquals('image', $result->getMediaType());

        $result = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['mediaType' => 'DESC']
        );
        $this->assertInstanceOf(ComplaintMedia::class, $result);
        $this->assertEquals('video', $result->getMediaType());
    }

    private function createComplaintMedia(string $mediaType = 'image', ?Complaint $complaint = null): ComplaintMedia
    {
        if (null === $complaint) {
            $complaint = $this->createComplaint();
            $this->persistAndFlush($complaint);
        }

        $complaintMedia = new ComplaintMedia();
        $complaintMedia->setMediaType($mediaType);
        $complaintMedia->setMediaUrl(['http://example.com/media1.jpg', 'http://example.com/media2.jpg']);
        $complaintMedia->setComplaint($complaint);

        return $complaintMedia;
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

    public function testFindOneByComplaintWithOrderBy(): void
    {
        $this->clearAllData();

        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $mediaA = $this->createComplaintMedia('a_type', $complaint);
        $mediaZ = $this->createComplaintMedia('z_type', $complaint);

        $this->persistAndFlush($mediaA);
        $this->persistAndFlush($mediaZ);

        $resultAsc = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['mediaType' => 'ASC']
        );
        $this->assertInstanceOf(ComplaintMedia::class, $resultAsc);
        $this->assertEquals('a_type', $resultAsc->getMediaType());

        $resultDesc = $this->repository->findOneBy(
            ['complaint' => $complaint],
            ['mediaType' => 'DESC']
        );
        $this->assertInstanceOf(ComplaintMedia::class, $resultDesc);
        $this->assertEquals('z_type', $resultDesc->getMediaType());
    }

    public function testFindByComplaintAssociation(): void
    {
        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $media1 = $this->createComplaintMedia('image', $complaint1);
        $media2 = $this->createComplaintMedia('video', $complaint2);

        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);

        $results = $this->repository->findBy(['complaint' => $complaint1]);
        $this->assertCount(1, $results);
        $this->assertContains($media1, $results);
        $this->assertNotContains($media2, $results);
    }

    public function testCountByComplaintAssociation(): void
    {
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $media1 = $this->createComplaintMedia('image', $complaint);
        $media2 = $this->createComplaintMedia('video', $complaint);

        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);

        $count = $this->repository->count(['complaint' => $complaint]);
        $this->assertEquals(2, $count);
    }

    public function testFindByMediaTypeIsNull(): void
    {
        $this->clearAllData();

        // 创建一些正常的实体
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $normalMedia1 = $this->createComplaintMedia('image', $complaint);
        $normalMedia2 = $this->createComplaintMedia('video', $complaint);
        $this->persistAndFlush($normalMedia1);
        $this->persistAndFlush($normalMedia2);

        // 由于 mediaType 字段在数据库中有 NOT NULL 约束，查询 null 值应该返回空数组
        $result = $this->repository->findBy(['mediaType' => null]);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testCountByMediaTypeIsNull(): void
    {
        $this->clearAllData();

        // 创建一些正常的实体
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $normalMedia1 = $this->createComplaintMedia('image', $complaint);
        $normalMedia2 = $this->createComplaintMedia('video', $complaint);
        $this->persistAndFlush($normalMedia1);
        $this->persistAndFlush($normalMedia2);

        // 由于 mediaType 字段在数据库中有 NOT NULL 约束，count null 值应该返回 0
        $count = $this->repository->count(['mediaType' => null]);

        $this->assertEquals(0, $count);
    }

    public function testFindByComplaintIsNull(): void
    {
        $this->clearAllData();

        // 由于 complaint 字段有 nullable: false 约束，我们无法在数据库层面创建 complaint 为 null 的记录
        // 但我们仍然可以测试查询逻辑，验证没有找到任何记录
        $result = $this->repository->findBy(['complaint' => null]);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testCountByComplaintIsNull(): void
    {
        $this->clearAllData();

        // 创建一些正常的实体
        $complaint = $this->createComplaint();
        $this->persistAndFlush($complaint);

        $media1 = $this->createComplaintMedia('image', $complaint);
        $media2 = $this->createComplaintMedia('video', $complaint);
        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);

        // 由于 complaint 字段有 nullable: false 约束，count 应该返回 0
        $count = $this->repository->count(['complaint' => null]);

        $this->assertEquals(0, $count);
    }

    public function testFindOneByAssociationComplaintShouldReturnMatchingEntity(): void
    {
        $this->clearAllData();

        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $media1 = $this->createComplaintMedia('image', $complaint1);
        $media2 = $this->createComplaintMedia('video', $complaint2);
        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);

        $result = $this->repository->findOneBy(['complaint' => $complaint1]);

        $this->assertInstanceOf(ComplaintMedia::class, $result);
        $complaint = $result->getComplaint();
        $this->assertInstanceOf(Complaint::class, $complaint);
        $this->assertEquals($complaint1->getId(), $complaint->getId());
        $this->assertEquals('image', $result->getMediaType());
    }

    public function testCountByAssociationComplaintShouldReturnCorrectNumber(): void
    {
        $this->clearAllData();

        $complaint1 = $this->createComplaint();
        $complaint2 = $this->createComplaint();
        $this->persistAndFlush($complaint1);
        $this->persistAndFlush($complaint2);

        $media1 = $this->createComplaintMedia('image', $complaint1);
        $media2 = $this->createComplaintMedia('video', $complaint1);
        $media3 = $this->createComplaintMedia('audio', $complaint1);
        $media4 = $this->createComplaintMedia('document', $complaint2);
        $this->persistAndFlush($media1);
        $this->persistAndFlush($media2);
        $this->persistAndFlush($media3);
        $this->persistAndFlush($media4);

        $count1 = $this->repository->count(['complaint' => $complaint1]);
        $count2 = $this->repository->count(['complaint' => $complaint2]);

        $this->assertEquals(3, $count1);
        $this->assertEquals(1, $count2);
    }

    protected function createNewEntity(): object
    {
        return $this->createComplaintMedia();
    }

    /**
     * @return ServiceEntityRepository<ComplaintMedia>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
