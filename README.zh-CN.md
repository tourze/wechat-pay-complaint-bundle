# 微信支付投诉处理组件

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-pay-complaint-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/wechat-pay-complaint-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-pay-complaint-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/wechat-pay-complaint-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-pay-complaint-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)]
(https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/wechat-pay-complaint-bundle.svg?style=flat-square)]
(https://scrutinizer-ci.com/g/tourze/wechat-pay-complaint-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)]
(https://codecov.io/gh/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-pay-complaint-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/wechat-pay-complaint-bundle)

一个全面的 Symfony 组件，用于处理微信支付消费者投诉并提供完整的工作流管理。该组件提供实体、
仓库和命令来管理与微信支付 API 集成的投诉处理流程。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [使用方法](#使用方法)
  - [快速开始](#快速开始)
  - [仓库使用](#仓库使用)
  - [处理投诉媒体](#处理投诉媒体)
- [高级用法](#高级用法)
- [命令](#命令)
- [实体](#实体)
- [安全性](#安全性)
- [依赖要求](#依赖要求)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- **完整的投诉实体管理** - 投诉、投诉媒体、投诉回复实体，具有完整的关系映射
- **自动投诉同步** - 从微信支付 API 获取投诉列表，支持定时任务
- **投诉状态管理** - 支持投诉状态（待处理、处理中、已处理）
- **媒体文件处理** - 管理投诉相关的媒体文件和附件
- **回复系统** - 完整的回复系统用于处理客户投诉
- **商户支持** - 多商户投诉管理
- **雪花 ID 集成** - 为所有实体生成唯一 ID
- **时间戳跟踪** - 自动创建和更新时间戳管理

## 安装

```bash
composer require tourze/wechat-pay-complaint-bundle
```

## 配置

该组件使用来自 `tourze/wechat-pay-bundle` 的微信支付配置。
确保您的商户已正确配置有效的证书和 API 凭据。

```yaml
# config/packages/wechat_pay.yaml
wechat_pay:
    merchants:
        default:
            mch_id: 'your_merchant_id'
            api_key: 'your_api_key'
            serial_number: 'your_certificate_serial_number'
            private_key_path: '%kernel.project_dir%/config/wechat/private.pem'
            certificate_path: '%kernel.project_dir%/config/wechat/certificate.pem'
```

## 使用方法

### 快速开始

### 组件注册

在 `config/bundles.php` 中添加组件：

```php
<?php

return [
    // ...
    WechatPayComplaintBundle\WechatPayComplaintBundle::class => ['all' => true],
];
```

### 基本实体使用

```php
<?php

use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;

// 创建新投诉
$complaint = new Complaint();
$complaint->setWxComplaintId('200201820200101080076610000');
$complaint->setComplaintState(ComplaintState::PENDING);
$complaint->setComplaintDetail('客户投诉详情...');
$complaint->setPayOrderNo('ORDER123456');
$complaint->setAmount(100.00);
```

### 仓库使用

```php
<?php

use WechatPayComplaintBundle\Repository\ComplaintRepository;
use WechatPayComplaintBundle\Enum\ComplaintState;

// 按状态查找投诉
$pendingComplaints = $complaintRepository->findBy([
    'complaintState' => ComplaintState::PENDING
]);

// 按商户查找投诉
$merchantComplaints = $complaintRepository->findBy([
    'merchant' => $merchant
]);

// 按日期范围查找投诉
$complaints = $complaintRepository->findComplaintsByDateRange(
    '2024-01-01',
    '2024-01-31'
);
```

### 处理投诉媒体

```php
<?php

use WechatPayComplaintBundle\Entity\ComplaintMedia;

// 访问投诉媒体
foreach ($complaint->getComplaintMedia() as $media) {
    echo $media->getMediaType();
    print_r($media->getMediaUrl());
}

// 为投诉添加新媒体
$media = new ComplaintMedia();
$media->setMediaType('image');
$media->setMediaUrl(['https://example.com/image.jpg']);
$complaint->addComplaintMedium($media);
```

## 高级用法

### 自定义投诉处理

```php
<?php

use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;
use WechatPayComplaintBundle\Enum\ComplaintState;

class ComplaintProcessor
{
    public function processComplaint(Complaint $complaint): void
    {
        // 更新投诉状态
        $complaint->setComplaintState(ComplaintState::PROCESSING);
        
        // 创建回复
        $reply = new ComplaintReply();
        $reply->setContent('我们正在处理您的投诉...');
        $reply->setComplaint($complaint);
        
        // 保存更改
        $this->entityManager->persist($reply);
        $this->entityManager->flush();
    }
}
```

### 使用事件的自动化工作流

```php
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WechatPayComplaintBundle\Event\ComplaintCreatedEvent;

class ComplaintWorkflowSubscriber implements EventSubscriberInterface
{
    public function onComplaintCreated(ComplaintCreatedEvent $event): void
    {
        $complaint = $event->getComplaint();
        
        // 自动分配给处理团队
        $this->assignToTeam($complaint);
        
        // 发送通知
        $this->notificationService->notifyNewComplaint($complaint);
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            ComplaintCreatedEvent::class => 'onComplaintCreated',
        ];
    }
}
```

### 定时任务集成

```yaml
# config/packages/tourze_symfony_cron_job.yaml
tourze_symfony_cron_job:
    tasks:
        - { command: 'wechat:pay:fetch-pay-complaint', schedule: '*/5 * * * *' }
```

## 命令

### 获取投诉列表

从微信支付 API 获取投诉列表：

```bash
# 获取今天的投诉
php bin/console wechat:pay:fetch-pay-complaint

# 获取指定日期范围的投诉
php bin/console wechat:pay:fetch-pay-complaint 2024-01-01 2024-01-31
```

**命令功能：**
- 从微信支付 API 获取投诉列表
- 自动在数据库中创建投诉实体
- 支持大数据集的分页
- 包含媒体文件同步
- 可配置为定时任务（默认每分钟运行一次）
- 处理多个商户
- 外部 API 交互的全面日志记录

## 实体

### Complaint（投诉）

主要投诉实体，包含以下关键字段：
- `wxComplaintId` - 微信投诉 ID（唯一）
- `complaintState` - 当前投诉状态（枚举）
- `complaintDetail` - 详细投诉描述
- `payOrderNo` - 本地订单号
- `wxPayOrderNo` - 微信订单号
- `amount` - 订单金额
- `applyRefundAmount` - 申请退款金额
- `complaintTime` - 投诉创建时间
- `payerPhone` - 投诉人电话号码
- `problemDescription` - 问题描述
- `complaintFullRefunded` - 全额退款状态

### ComplaintMedia（投诉媒体）

存储与投诉相关的媒体文件：
- `mediaType` - 媒体类型（图片、视频等）
- `mediaUrl` - 媒体 URL 数组
- 通过外键关联到投诉

### ComplaintReply（投诉回复）

管理投诉回复：
- `content` - 回复内容
- 通过外键关联到投诉

### 枚举

#### ComplaintState（投诉状态）

定义投诉处理状态：
- `PENDING` - 新创建，等待处理
- `PROCESSING` - 正在处理中
- `PROCESSED` - 处理完成

## 安全性

### 数据保护

- 所有敏感数据都使用 Symfony 验证器进行适当验证
- 电话号码使用正则表达式模式验证
- 字符串字段具有长度约束以防止缓冲区溢出
- 实体关系使用适当的外键约束

### API 安全

- 微信支付 API 调用使用证书进行身份验证
- 请求/响应日志包括敏感数据的清理
- 错误处理防止信息泄露
- 外部系统交互的全面审计日志

### 最佳实践

- 对敏感配置使用环境变量
- 定期轮换 API 凭据
- 在应用程序中实施适当的访问控制
- 监控和记录所有投诉相关活动

## 依赖要求

此组件需要：

- **PHP 8.1+** - 支持类型声明和枚举的现代 PHP
- **Symfony 6.4+** - 依赖注入、控制台和配置的框架组件
- **Doctrine ORM 3.0+** - 数据库抽象和实体管理
- **tourze/wechat-pay-bundle** - 核心微信支付 API 集成
- **tourze/doctrine-timestamp-bundle** - 自动时间戳管理

## 测试

运行测试套件：

```bash
# 运行所有测试
vendor/bin/phpunit packages/wechat-pay-complaint-bundle/tests

# 运行单元测试
vendor/bin/phpunit packages/wechat-pay-complaint-bundle/tests --filter Unit

# 运行覆盖率测试
vendor/bin/phpunit packages/wechat-pay-complaint-bundle/tests --coverage-html coverage
```

### 测试说明

测试套件主要专注于实体功能、业务逻辑验证和组件验证的单元测试。
集成测试在应用层级处理，那里所有依赖都可以正确配置。

## 贡献

有关如何为此项目贡献的详细信息，请参阅 [CONTRIBUTING.md](CONTRIBUTING.md)。

### 开发环境设置

1. 克隆仓库
2. 安装依赖：`composer install`
3. 运行测试：`vendor/bin/phpunit`
4. 检查代码风格：`vendor/bin/phpstan analyse`

## 许可证

MIT 许可证 (MIT)。更多信息请参阅 [许可证文件](LICENSE)。

## 文档

详细的 API 文档，请参考：
- [微信支付投诉 API v2]
  (https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/list-complaints-v2.html)
- [微信支付投诉媒体 API]
  (https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaint-media/download-complaint-media.html)