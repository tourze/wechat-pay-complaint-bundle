<?php

namespace WechatPayComplaintBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use WechatPayComplaintBundle\Repository\ComplaintReplyRepository;

/**
 * 微信支付-投诉回复
 */
#[ORM\Entity(repositoryClass: ComplaintReplyRepository::class)]
#[ORM\Table(name: 'wechat_payment_complaint_reply', options: ['comment' => '微信支付-投诉回复'])]
class ComplaintReply
{
    use TimestampableAware;

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private string $content;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'complaintReply')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Complaint $complaint = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getComplaint(): ?Complaint
    {
        return $this->complaint;
    }

    public function setComplaint(?Complaint $complaint): void
    {
        $this->complaint = $complaint;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
