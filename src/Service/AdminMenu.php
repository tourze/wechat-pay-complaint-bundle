<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatPayComplaintBundle\Controller\Admin\ComplaintCrudController;
use WechatPayComplaintBundle\Controller\Admin\ComplaintMediaCrudController;
use WechatPayComplaintBundle\Controller\Admin\ComplaintReplyCrudController;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信支付投诉管理')) {
            $item->addChild('微信支付投诉管理')->setExtra('permission', 'ROLE_ADMIN');
        }

        $complaintMenu = $item->getChild('微信支付投诉管理');
        if (null !== $complaintMenu) {
            $complaintMenu->addChild('投诉管理')->setUri($this->linkGenerator->getCurdListPage(ComplaintCrudController::class));
            $complaintMenu->addChild('投诉回复')->setUri($this->linkGenerator->getCurdListPage(ComplaintReplyCrudController::class));
            $complaintMenu->addChild('投诉资料')->setUri($this->linkGenerator->getCurdListPage(ComplaintMediaCrudController::class));
        }
    }
}
