<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatPayComplaintBundle\Controller\Admin\ComplaintMediaCrudController;
use WechatPayComplaintBundle\Entity\ComplaintMedia;

/**
 * @internal
 */
#[CoversClass(ComplaintMediaCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ComplaintMediaCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): ComplaintMediaCrudController
    {
        return self::getService(ComplaintMediaCrudController::class);
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield '关联投诉' => ['关联投诉'];
        yield '媒体类型' => ['媒体类型'];
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

    public function testConfigureFields(): void
    {
        $controller = new ComplaintMediaCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(3, count($fields));
    }

    /**
     * 测试ArrayField字段配置是否正确
     */
    public function testArrayFieldConfiguration(): void
    {
        $controller = $this->getControllerService();
        $fields = iterator_to_array($controller->configureFields('new'));

        $mediaUrlFieldExists = false;
        foreach ($fields as $field) {
            if (is_string($field)) {
                continue;
            }
            $dto = $field->getAsDto();
            if ('mediaUrl' === $dto->getProperty()) {
                $mediaUrlFieldExists = true;
                // 验证这是一个ArrayField配置
                $this->assertTrue($dto->isDisplayedOn('new'), 'mediaUrl字段应该在new页面显示');
                $this->assertFalse($dto->isDisplayedOn('index'), 'mediaUrl字段应该在index页面隐藏');
                break;
            }
        }

        $this->assertTrue($mediaUrlFieldExists, 'mediaUrl ArrayField应该被正确配置');
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // NEW action may be available for this controller
        // Note: mediaUrl is ArrayField and has special rendering, tested separately
        yield 'complaint' => ['complaint'];
        yield 'mediaType' => ['mediaType'];
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
