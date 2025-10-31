<?php

namespace WechatPayComplaintBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatPayComplaintBundle\Enum\ComplaintState;

/**
 * @internal
 */
#[CoversClass(ComplaintState::class)]
final class ComplaintStateTest extends AbstractEnumTestCase
{
    #[TestWith(['PENDING', '待处理'])]
    #[TestWith(['PROCESSING', '处理中'])]
    #[TestWith(['PROCESSED', '已处理完成'])]
    public function testValueAndLabel(string $expectedValue, string $expectedLabel): void
    {
        $case = ComplaintState::from($expectedValue);
        $this->assertEquals($expectedValue, $case->value);
        $this->assertEquals($expectedLabel, $case->getLabel());
    }

    #[TestWith(['PENDING'])]
    #[TestWith(['PROCESSING'])]
    #[TestWith(['PROCESSED'])]
    public function testTryFromValidValues(string $value): void
    {
        $case = ComplaintState::tryFrom($value);
        $this->assertInstanceOf(ComplaintState::class, $case);
        $this->assertEquals($value, $case->value);
    }

    #[TestWith(['INVALID_VALUE'])]
    #[TestWith(['UNKNOWN'])]
    #[TestWith([''])]
    public function testTryFromInvalidValues(string $value): void
    {
        $result = ComplaintState::tryFrom($value);
        $this->assertNull($result);
    }

    #[TestWith(['INVALID_VALUE'])]
    #[TestWith(['UNKNOWN'])]
    #[TestWith([''])]
    public function testFromExceptionHandling(string $value): void
    {
        $this->expectException(\ValueError::class);
        ComplaintState::from($value);
    }

    public function testValueUniqueness(): void
    {
        $cases = ComplaintState::cases();
        $values = array_map(fn ($case) => $case->value, $cases);
        $uniqueValues = array_unique($values);

        $this->assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $cases = ComplaintState::cases();
        $labels = array_map(fn ($case) => $case->getLabel(), $cases);
        $uniqueLabels = array_unique($labels);

        $this->assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
    }

    public function testToArray(): void
    {
        $this->assertEquals([
            'value' => 'PENDING',
            'label' => '待处理',
        ], ComplaintState::PENDING->toArray());

        $this->assertEquals([
            'value' => 'PROCESSING',
            'label' => '处理中',
        ], ComplaintState::PROCESSING->toArray());

        $this->assertEquals([
            'value' => 'PROCESSED',
            'label' => '已处理完成',
        ], ComplaintState::PROCESSED->toArray());
    }
}
