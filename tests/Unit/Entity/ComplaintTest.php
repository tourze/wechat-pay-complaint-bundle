<?php

namespace WechatPayComplaintBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;
use WechatPayComplaintBundle\Enum\ComplaintState;

class ComplaintTest extends TestCase
{
    private Complaint $complaint;
    private Merchant $merchant;

    protected function setUp(): void
    {
        $this->complaint = new Complaint();
        $this->merchant = $this->createMock(Merchant::class);
    }

    public function testSetGetMerchant(): void
    {
        $this->complaint->setMerchant($this->merchant);
        $this->assertSame($this->merchant, $this->complaint->getMerchant());
    }

    public function testSetGetWxComplaintId(): void
    {
        $wxComplaintId = 'wx_12345678';
        $this->complaint->setWxComplaintId($wxComplaintId);
        $this->assertEquals($wxComplaintId, $this->complaint->getWxComplaintId());
    }

    public function testSetGetComplaintState(): void
    {
        $state = ComplaintState::PENDING;
        $this->complaint->setComplaintState($state);
        $this->assertSame($state, $this->complaint->getComplaintState());
    }

    public function testSetGetPayerPhone(): void
    {
        $phone = '13800138000';
        $this->complaint->setPayerPhone($phone);
        $this->assertEquals($phone, $this->complaint->getPayerPhone());
    }

    public function testSetGetPayOrderNo(): void
    {
        $orderNo = 'ORDER12345';
        $this->complaint->setPayOrderNo($orderNo);
        $this->assertEquals($orderNo, $this->complaint->getPayOrderNo());
    }

    public function testSetGetWxPayOrderNo(): void
    {
        $wxOrderNo = 'WXORDER12345';
        $this->complaint->setWxPayOrderNo($wxOrderNo);
        $this->assertEquals($wxOrderNo, $this->complaint->getWxPayOrderNo());
    }

    public function testSetGetAmount(): void
    {
        $amount = 123.45;
        $this->complaint->setAmount($amount);
        $this->assertEquals($amount, $this->complaint->getAmount());
    }

    public function testSetGetUserComplaintTimes(): void
    {
        $times = 2;
        $this->complaint->setUserComplaintTimes($times);
        $this->assertEquals($times, $this->complaint->getUserComplaintTimes());
    }

    public function testSetGetApplyRefundAmount(): void
    {
        $amount = 50.25;
        $this->complaint->setApplyRefundAmount($amount);
        $this->assertEquals($amount, $this->complaint->getApplyRefundAmount());
    }

    public function testSetGetRawData(): void
    {
        $rawData = '{"key": "value"}';
        $this->complaint->setRawData($rawData);
        $this->assertEquals($rawData, $this->complaint->getRawData());
    }

    public function testSetGetComplaintDetail(): void
    {
        $detail = '商品质量问题';
        $this->complaint->setComplaintDetail($detail);
        $this->assertEquals($detail, $this->complaint->getComplaintDetail());
    }

    public function testSetGetProblemDescription(): void
    {
        $description = '商品存在破损';
        $this->complaint->setProblemDescription($description);
        $this->assertEquals($description, $this->complaint->getProblemDescription());
    }

    public function testSetIsComplaintFullRefunded(): void
    {
        $isRefunded = true;
        $this->complaint->setComplaintFullRefunded($isRefunded);
        $this->assertTrue($this->complaint->isComplaintFullRefunded());
    }

    public function testSetGetComplaintTime(): void
    {
        $time = '2023-10-01 12:00:00';
        $this->complaint->setComplaintTime($time);
        $this->assertEquals($time, $this->complaint->getComplaintTime());
    }

    public function testInitializeComplaintMediaCollection(): void
    {
        $collection = $this->complaint->getComplaintMedia();
        $this->assertNotNull($collection);
        $this->assertCount(0, $collection);
    }

    public function testAddRemoveComplaintMedia(): void
    {
        $media = $this->createMock(ComplaintMedia::class);
        
        // 测试添加
        $this->complaint->addComplaintMedium($media);
        $this->assertCount(1, $this->complaint->getComplaintMedia());
        $this->assertTrue($this->complaint->getComplaintMedia()->contains($media));
        
        // 测试移除
        $this->complaint->removeComplaintMedium($media);
        $this->assertCount(0, $this->complaint->getComplaintMedia());
        $this->assertFalse($this->complaint->getComplaintMedia()->contains($media));
    }

    public function testSetGetCreateTime(): void
    {
        $time = new \DateTimeImmutable();
        $this->complaint->setCreateTime($time);
        $this->assertSame($time, $this->complaint->getCreateTime());
    }

    public function testSetGetUpdateTime(): void
    {
        $time = new \DateTimeImmutable();
        $this->complaint->setUpdateTime($time);
        $this->assertSame($time, $this->complaint->getUpdateTime());
    }
} 