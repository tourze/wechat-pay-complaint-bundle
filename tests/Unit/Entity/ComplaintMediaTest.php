<?php

namespace WechatPayComplaintBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;

class ComplaintMediaTest extends TestCase
{
    private ComplaintMedia $complaintMedia;
    private Complaint $complaint;

    protected function setUp(): void
    {
        $this->complaintMedia = new ComplaintMedia();
        $this->complaint = $this->createMock(Complaint::class);
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
        $time = new \DateTime();
        $this->complaintMedia->setCreateTime($time);
        $this->assertSame($time, $this->complaintMedia->getCreateTime());
    }

    public function testSetGetUpdateTime(): void
    {
        $time = new \DateTime();
        $this->complaintMedia->setUpdateTime($time);
        $this->assertSame($time, $this->complaintMedia->getUpdateTime());
    }
} 