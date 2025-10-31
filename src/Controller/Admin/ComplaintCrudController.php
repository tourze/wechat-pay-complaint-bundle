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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;

#[AdminCrud(routePath: '/wechat-pay-complaint/complaint', routeName: 'wechat_pay_complaint_complaint')]
final class ComplaintCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Complaint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('投诉')
            ->setEntityLabelInPlural('投诉管理')
            ->setSearchFields(['wxComplaintId', 'payOrderNo', 'wxPayOrderNo', 'payerPhone', 'complaintDetail', 'problemDescription'])
            ->setDefaultSort(['updateTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::EDIT, Action::NEW, Action::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('merchant', '商户')
            ->setRequired(true)
            ->setHelp('选择关联的微信支付商户')
            ->autocomplete()
        ;

        yield TextField::new('wxComplaintId', '投诉单号')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('微信支付系统生成的投诉单号')
            ->setColumns(6)
        ;

        yield TextField::new('complaintTime', '投诉时间')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('用户发起投诉的时间')
            ->setColumns(6)
        ;

        yield ChoiceField::new('complaintState', '投诉状态')
            ->setChoices([
                '待处理' => ComplaintState::PENDING,
                '处理中' => ComplaintState::PROCESSING,
                '已处理完成' => ComplaintState::PROCESSED,
            ])
            ->setRequired(true)
            ->setHelp('当前投诉处理状态')
            ->setColumns(6)
            ->renderAsBadges([
                ComplaintState::PENDING->value => 'warning',
                ComplaintState::PROCESSING->value => 'info',
                ComplaintState::PROCESSED->value => 'success',
            ])
        ;

        yield TextField::new('payerPhone', '投诉人联系方式')
            ->setMaxLength(11)
            ->setHelp('投诉人的手机号码，用于联系沟通')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('payOrderNo', '本地订单号')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('系统内部的订单编号')
            ->setColumns(6)
        ;

        yield TextField::new('wxPayOrderNo', '微信订单号')
            ->setMaxLength(100)
            ->setHelp('微信支付系统的订单号')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield MoneyField::new('amount', '订单金额')
            ->setRequired(true)
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setHelp('原订单的支付金额')
            ->setColumns(6)
        ;

        yield MoneyField::new('applyRefundAmount', '申请退款金额')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setHelp('用户申请退款的金额')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield NumberField::new('userComplaintTimes', '投诉次数')
            ->setHelp('该用户的历史投诉次数')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield BooleanField::new('complaintFullRefunded', '是否已全额退款')
            ->setHelp('投诉单是否已经进行了全额退款')
            ->hideOnIndex()
        ;

        yield TextareaField::new('complaintDetail', '投诉详情')
            ->setMaxLength(300)
            ->setHelp('投诉的详细描述内容')
            ->hideOnIndex()
            ->setNumOfRows(3)
        ;

        yield TextareaField::new('problemDescription', '问题描述')
            ->setMaxLength(255)
            ->setHelp('问题的具体描述')
            ->hideOnIndex()
            ->setNumOfRows(3)
        ;

        yield TextareaField::new('rawData', '原始数据')
            ->setHelp('从微信接口获取的原始JSON数据')
            ->onlyOnDetail()
            ->setNumOfRows(5)
        ;

        yield AssociationField::new('complaintMedia', '投诉资料')
            ->setHelp('与该投诉相关的媒体文件')
            ->onlyOnDetail()
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
            ->add(EntityFilter::new('merchant', '商户'))
            ->add(TextFilter::new('wxComplaintId', '投诉单号'))
            ->add(ChoiceFilter::new('complaintState', '投诉状态')
                ->setChoices(ComplaintState::cases()))
            ->add(TextFilter::new('payOrderNo', '本地订单号'))
            ->add(TextFilter::new('payerPhone', '投诉人手机'))
            ->add(NumericFilter::new('amount', '订单金额'))
            ->add(BooleanFilter::new('complaintFullRefunded', '是否已全额退款'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
