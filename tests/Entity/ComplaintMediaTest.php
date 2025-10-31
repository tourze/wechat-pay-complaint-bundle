<?php

namespace WechatPayComplaintBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;

/**
 * @internal
 */
#[CoversClass(ComplaintMedia::class)]
final class ComplaintMediaTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ComplaintMedia();
    }

    /**
     * @return iterable<string, array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'mediaUrl' => ['mediaUrl', ['key' => 'value']],
        ];
    }

    private ComplaintMedia $complaintMedia;

    private Complaint $complaint;

    protected function setUp(): void
    {
        parent::setUp();
        $this->complaintMedia = new ComplaintMedia();
        $this->complaint = new Complaint();
    }

    public function testSetGetMediaUrl(): void
    {
        $url = ['https://example.com/media/12345.jpg'];
        $this->complaintMedia->setMediaUrl($url);
        $this->assertEquals($url, $this->complaintMedia->getMediaUrl());
    }

    public function testSetGetMediaType(): void
    {
        $type = 'image';
        $this->complaintMedia->setMediaType($type);
        $this->assertEquals($type, $this->complaintMedia->getMediaType());
    }

    public function testSetGetComplaint(): void
    {
        $this->complaintMedia->setComplaint($this->complaint);
        $this->assertSame($this->complaint, $this->complaintMedia->getComplaint());
    }

    public function testSetGetCreateTime(): void
    {
        $time = new \DateTimeImmutable();
        $this->complaintMedia->setCreateTime($time);
        $this->assertSame($time, $this->complaintMedia->getCreateTime());
    }

    public function testSetGetUpdateTime(): void
    {
        $time = new \DateTimeImmutable();
        $this->complaintMedia->setUpdateTime($time);
        $this->assertSame($time, $this->complaintMedia->getUpdateTime());
    }
}
