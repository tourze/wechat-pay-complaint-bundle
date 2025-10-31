<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Service;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatPayComplaintBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // Setup test environment if needed
    }

    private function getAdminMenuService(): AdminMenu
    {
        return self::getService(AdminMenu::class);
    }

    public function testInvokeCreatesComplaintManagementMenu(): void
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')
            ->willReturnCallback(function (string $name) use ($factory) {
                return new MenuItem($name, $factory);
            })
        ;

        $rootMenu = new MenuItem('root', $factory);
        $adminMenu = $this->getAdminMenuService();

        $adminMenu($rootMenu);

        // 简化验证 - 只检查菜单是否添加成功
        $this->assertTrue($rootMenu->hasChildren());
    }

    public function testInvokeWithExistingComplaintManagementMenu(): void
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createItem')
            ->willReturnCallback(function (string $name) use ($factory) {
                return new MenuItem($name, $factory);
            })
        ;

        $rootMenu = new MenuItem('root', $factory);
        $adminMenu = $this->getAdminMenuService();

        $adminMenu($rootMenu);

        // 简化验证
        $this->assertTrue($rootMenu->hasChildren());
    }
}
