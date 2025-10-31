<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatPayComplaintBundle\DependencyInjection\WechatPayComplaintExtension;

/**
 * @internal
 */
#[CoversClass(WechatPayComplaintExtension::class)]
final class WechatPayComplaintExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
