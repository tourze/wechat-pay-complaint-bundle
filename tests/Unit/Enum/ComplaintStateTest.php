<?php

namespace WechatPayComplaintBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use WechatPayComplaintBundle\Enum\ComplaintState;

class ComplaintStateTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertEquals('PENDING', ComplaintState::PENDING->value);
        $this->assertEquals('PROCESSING', ComplaintState::PROCESSING->value);
        $this->assertEquals('PROCESSED', ComplaintState::PROCESSED->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('待处理', ComplaintState::PENDING->getLabel());
        $this->assertEquals('处理中', ComplaintState::PROCESSING->getLabel());
        $this->assertEquals('已处理完成', ComplaintState::PROCESSED->getLabel());
    }

    public function testTryFrom(): void
    {
        $this->assertSame(ComplaintState::PENDING, ComplaintState::tryFrom('PENDING'));
        $this->assertSame(ComplaintState::PROCESSING, ComplaintState::tryFrom('PROCESSING'));
        $this->assertSame(ComplaintState::PROCESSED, ComplaintState::tryFrom('PROCESSED'));
        $this->assertNull(ComplaintState::tryFrom('INVALID_VALUE'));
    }
} 