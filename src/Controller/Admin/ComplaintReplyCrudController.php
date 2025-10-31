<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;

#[AdminCrud(routePath: '/wechat-pay-complaint/complaint-reply', routeName: 'wechat_pay_complaint_complaint_reply')]
final class ComplaintReplyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComplaintReply::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('投诉回复')
            ->setEntityLabelInPlural('投诉回复管理')
            ->setSearchFields(['content'])
            ->setDefaultSort(['updateTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('complaint', '关联投诉')
            ->setRequired(true)
            ->setHelp('选择要回复的投诉单')
            ->autocomplete()
            ->formatValue(function ($value) {
                return $value instanceof Complaint
                    ? '投诉单号: ' . $value->getWxComplaintId()
                    : '';
            })
        ;

        yield TextareaField::new('content', '回复内容')
            ->setRequired(true)
            ->setMaxLength(255)
            ->setHelp('针对投诉的回复内容，最多255个字符')
            ->setNumOfRows(4)
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('complaint', '关联投诉'))
            ->add(TextFilter::new('content', '回复内容'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
