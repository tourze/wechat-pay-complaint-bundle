# WeChat Pay Complaint Bundle

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

A comprehensive Symfony bundle for handling WeChat Pay consumer complaints with complete 
workflow management. This bundle provides entities, repositories, and commands to manage 
WeChat Pay complaint processes integrated with the WeChat Pay API.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Quick Start](#quick-start)
  - [Repository Usage](#repository-usage)
  - [Working with Complaint Media](#working-with-complaint-media)
- [Advanced Usage](#advanced-usage)
- [Commands](#commands)
- [Entities](#entities)
- [Security](#security)
- [Dependencies](#dependencies)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Complete complaint entity management** - Complaint, ComplaintMedia, ComplaintReply 
  entities with full relationship mapping
- **Automated complaint synchronization** - Fetch complaint lists from WeChat Pay API 
  with cron job support
- **Complaint state management** - Support for complaint states (PENDING, PROCESSING, 
  PROCESSED)
- **Media file handling** - Manage complaint-related media files and attachments
- **Reply system** - Complete reply system for handling customer complaints
- **Merchant support** - Multi-merchant complaint management
- **Snowflake ID integration** - Unique ID generation for all entities
- **Timestamp tracking** - Automatic creation and update timestamp management

## Installation

```bash
composer require tourze/wechat-pay-complaint-bundle
```

## Configuration

The bundle uses the WeChat Pay configuration from `tourze/wechat-pay-bundle`. 
Ensure your merchants are properly configured with valid certificates and API credentials.

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

## Usage

### Quick Start

### Bundle Registration

Add the bundle to your `config/bundles.php`:

```php
<?php

return [
    // ...
    WechatPayComplaintBundle\WechatPayComplaintBundle::class => ['all' => true],
];
```

### Basic Entity Usage

```php
<?php

use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;

// Create a new complaint
$complaint = new Complaint();
$complaint->setWxComplaintId('200201820200101080076610000');
$complaint->setComplaintState(ComplaintState::PENDING);
$complaint->setComplaintDetail('Customer complaint details...');
$complaint->setPayOrderNo('ORDER123456');
$complaint->setAmount(100.00);
```

### Repository Usage

```php
<?php

use WechatPayComplaintBundle\Repository\ComplaintRepository;
use WechatPayComplaintBundle\Enum\ComplaintState;

// Find complaints by state
$pendingComplaints = $complaintRepository->findBy([
    'complaintState' => ComplaintState::PENDING
]);

// Find complaints by merchant
$merchantComplaints = $complaintRepository->findBy([
    'merchant' => $merchant
]);

// Find complaints by date range
$complaints = $complaintRepository->findComplaintsByDateRange(
    '2024-01-01',
    '2024-01-31'
);
```

### Working with Complaint Media

```php
<?php

use WechatPayComplaintBundle\Entity\ComplaintMedia;

// Access complaint media
foreach ($complaint->getComplaintMedia() as $media) {
    echo $media->getMediaType();
    print_r($media->getMediaUrl());
}

// Add new media to complaint
$media = new ComplaintMedia();
$media->setMediaType('image');
$media->setMediaUrl(['https://example.com/image.jpg']);
$complaint->addComplaintMedium($media);
```

## Advanced Usage

### Custom Complaint Processing

```php
<?php

use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;
use WechatPayComplaintBundle\Enum\ComplaintState;

class ComplaintProcessor
{
    public function processComplaint(Complaint $complaint): void
    {
        // Update complaint state
        $complaint->setComplaintState(ComplaintState::PROCESSING);
        
        // Create a reply
        $reply = new ComplaintReply();
        $reply->setContent('We are processing your complaint...');
        $reply->setComplaint($complaint);
        
        // Save changes
        $this->entityManager->persist($reply);
        $this->entityManager->flush();
    }
}
```

### Automated Workflow with Events

```php
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WechatPayComplaintBundle\Event\ComplaintCreatedEvent;

class ComplaintWorkflowSubscriber implements EventSubscriberInterface
{
    public function onComplaintCreated(ComplaintCreatedEvent $event): void
    {
        $complaint = $event->getComplaint();
        
        // Automatically assign to processing team
        $this->assignToTeam($complaint);
        
        // Send notification
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

### Cron Job Integration

```yaml
# config/packages/tourze_symfony_cron_job.yaml
tourze_symfony_cron_job:
    tasks:
        - { command: 'wechat:pay:fetch-pay-complaint', schedule: '*/5 * * * *' }
```

## Commands

### Fetch Complaint List

Fetch complaint list from WeChat Pay API:

```bash
# Fetch complaints for today
php bin/console wechat:pay:fetch-pay-complaint

# Fetch complaints for specific date range
php bin/console wechat:pay:fetch-pay-complaint 2024-01-01 2024-01-31
```

**Command Features:**
- Fetches complaint list from WeChat Pay API
- Automatically creates complaint entities in the database
- Supports pagination for large datasets
- Includes media file synchronization
- Can be configured as a cron job (runs every minute by default)
- Handles multiple merchants
- Comprehensive logging of external API interactions

## Entities

### Complaint

Main complaint entity with the following key fields:
- `wxComplaintId` - WeChat complaint ID (unique)
- `complaintState` - Current complaint state (enum)
- `complaintDetail` - Detailed complaint description
- `payOrderNo` - Local order number
- `wxPayOrderNo` - WeChat order number
- `amount` - Order amount
- `applyRefundAmount` - Requested refund amount
- `complaintTime` - Complaint creation time
- `payerPhone` - Complainant phone number
- `problemDescription` - Problem description
- `complaintFullRefunded` - Full refund status

### ComplaintMedia

Stores media files related to complaints:
- `mediaType` - Media type (image, video, etc.)
- `mediaUrl` - Array of media URLs
- Related to complaint via foreign key

### ComplaintReply

Manages replies to complaints:
- `content` - Reply content
- Related to complaint via foreign key

### Enums

#### ComplaintState

Defines complaint processing states:
- `PENDING` - Newly created, awaiting processing
- `PROCESSING` - Currently being processed  
- `PROCESSED` - Processing completed

## Security

### Data Protection

- All sensitive data is properly validated using Symfony validators
- Phone numbers are validated with regex patterns
- String fields have length constraints to prevent buffer overflow
- Entity relationships use proper foreign key constraints

### API Security

- WeChat Pay API calls are authenticated using certificates
- Request/response logging includes sanitization of sensitive data
- Error handling prevents information leakage
- Comprehensive audit logging for external system interactions

### Best Practices

- Use environment variables for sensitive configuration
- Regularly rotate API credentials
- Implement proper access controls in your application
- Monitor and log all complaint-related activities

## Dependencies

This package requires:

- **PHP 8.1+** - Modern PHP with type declarations and enums support
- **Symfony 6.4+** - Framework components for DI, console, and configuration
- **Doctrine ORM 3.0+** - Database abstraction and entity management
- **tourze/wechat-pay-bundle** - Core WeChat Pay API integration
- **tourze/doctrine-timestamp-bundle** - Automatic timestamp management

## Testing

Run the test suite:

```bash
# Run all tests
vendor/bin/phpunit packages/wechat-pay-complaint-bundle/tests

# Run unit tests
vendor/bin/phpunit packages/wechat-pay-complaint-bundle/tests --filter Unit

# Run with coverage
vendor/bin/phpunit packages/wechat-pay-complaint-bundle/tests --coverage-html coverage
```

### Test Notes

The test suite focuses on unit tests for entity functionality, business logic validation, and component verification.
Integration tests are handled at the application level where all dependencies can be properly configured.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `vendor/bin/phpunit`
4. Check code style: `vendor/bin/phpstan analyse`

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Documentation

For detailed API documentation, please refer to:
- [WeChat Pay Complaint API v2]
  (https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaints/list-complaints-v2.html)
- [WeChat Pay Complaint Media API]
  (https://pay.weixin.qq.com/docs/merchant/apis/consumer-complaint/complaint-media/download-complaint-media.html)