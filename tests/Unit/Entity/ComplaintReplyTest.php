<?php

namespace WechatPayComplaintBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;

class ComplaintReplyTest extends TestCase
{
    private ComplaintReply $complaintReply;
    private Complaint $complaint;

    protected function setUp(): void
    {
        $this->complaintReply = new ComplaintReply();
        $this->complaint = $this->createMock(Complaint::class);
    }

    public function testSetGetComplaint(): void
    {
        $this->complaintReply->setComplaint($this->complaint);
        $this->assertSame($this->complaint, $this->complaintReply->getComplaint());
    }

    public function testSetGetContent(): void
    {
        $content = '我们已经处理了您的投诉';
        $this->complaintReply->setContent($content);
        $this->assertEquals($content, $this->complaintReply->getContent());
    }

    public function testSetGetCreateTime(): void
    {
        $time = new \DateTime();
        $this->complaintReply->setCreateTime($time);
        $this->assertSame($time, $this->complaintReply->getCreateTime());
    }

    public function testSetGetUpdateTime(): void
    {
        $time = new \DateTime();
        $this->complaintReply->setUpdateTime($time);
        $this->assertSame($time, $this->complaintReply->getUpdateTime());
    }
} 