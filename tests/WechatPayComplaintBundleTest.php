<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatPayComplaintBundle\WechatPayComplaintBundle;

/**
 * @internal
 */
#[CoversClass(WechatPayComplaintBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatPayComplaintBundleTest extends AbstractBundleTestCase
{
}
