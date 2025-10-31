<?php

declare(strict_types=1);

namespace WechatPayComplaintBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;

#[AdminCrud(routePath: '/wechat-pay-complaint/complaint-media', routeName: 'wechat_pay_complaint_complaint_media')]
final class ComplaintMediaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComplaintMedia::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('投诉资料')
            ->setEntityLabelInPlural('投诉资料管理')
            ->setSearchFields(['mediaType'])
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
            ->setHelp('选择要关联的投诉单')
            ->autocomplete()
            ->formatValue(function ($value) {
                return $value instanceof Complaint
                    ? '投诉单号: ' . $value->getWxComplaintId()
                    : '';
            })
        ;

        yield TextField::new('mediaType', '媒体类型')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('媒体文件的类型，如图片、视频等')
            ->setColumns(6)
        ;

        yield ArrayField::new('mediaUrl', '媒体URL列表')
            ->setRequired(true)
            ->setHelp('媒体文件的URL地址列表，支持多个文件')
            ->hideOnIndex()
            ->setTemplatePath('@WechatPayComplaint/admin/field/media_url_array.html.twig')
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
            ->add(TextFilter::new('mediaType', '媒体类型'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
