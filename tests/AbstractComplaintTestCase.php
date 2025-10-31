<?php

namespace WechatPayComplaintBundle\Tests;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(AbstractComplaintTestCase::class)]
abstract class AbstractComplaintTestCase extends TestCase
{
    protected function configureContainer(ContainerBuilder $container): void
    {
        $container->set('filesystem.operator', $this->createMockFilesystemOperator());
    }

    protected function createMockFilesystemOperator(): FilesystemOperator
    {
        return new MockFilesystemOperator();
    }
}
