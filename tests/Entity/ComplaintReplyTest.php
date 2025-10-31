<?php

namespace WechatPayComplaintBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;

/**
 * @internal
 */
#[CoversClass(ComplaintReply::class)]
final class ComplaintReplyTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ComplaintReply();
    }

    /**
     * @return iterable<string, array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            // id 是 Snowflake 字符串类型，由系统自动生成，不在此测试
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    private ComplaintReply $complaintReply;

    private Complaint $complaint;

    protected function setUp(): void
    {
        parent::setUp();
        $this->complaintReply = new ComplaintReply();
        $this->complaint = new Complaint();
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
        $time = new \DateTimeImmutable();
        $this->complaintReply->setCreateTime($time);
        $this->assertSame($time, $this->complaintReply->getCreateTime());
    }

    public function testSetGetUpdateTime(): void
    {
        $time = new \DateTimeImmutable();
        $this->complaintReply->setUpdateTime($time);
        $this->assertSame($time, $this->complaintReply->getUpdateTime());
    }
}
