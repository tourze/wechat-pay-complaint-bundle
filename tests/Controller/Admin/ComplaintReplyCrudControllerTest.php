<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatPayComplaintBundle\Controller\Admin\ComplaintReplyCrudController;
use WechatPayComplaintBundle\Entity\ComplaintReply;

/**
 * @internal
 */
#[CoversClass(ComplaintReplyCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ComplaintReplyCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): ComplaintReplyCrudController
    {
        return self::getService(ComplaintReplyCrudController::class);
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield '关联投诉' => ['关联投诉'];
        yield '回复内容' => ['回复内容'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(ComplaintReply::class, ComplaintReplyCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new ComplaintReplyCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(3, count($fields));
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // NEW action may be available for this controller
        yield 'complaint' => ['complaint'];
        yield 'content' => ['content'];
    }

    /**
     * 验证测试方法
     */
    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();
        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString('should not be blank', $crawler->filter('.invalid-feedback')->text());
    }
}
