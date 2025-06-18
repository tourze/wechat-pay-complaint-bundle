<?php

namespace WechatPayComplaintBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;

/**
 * 微信支付-投诉资料
 */
#[ORM\Entity(repositoryClass: ComplaintMediaRepository::class)]
#[ORM\Table(name: 'wechat_payment_complaint_media', options: ['comment' => '微信支付-投诉资料'])]
class ComplaintMedia
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(length: 100)]
    private ?string $mediaType = null;

    #[ORM\Column(type: Types::JSON)]
    private array $mediaUrl = [];

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'complaintMedia')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Complaint $complaint = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getMediaUrl(): array
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(array $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function getComplaint(): ?Complaint
    {
        return $this->complaint;
    }

    public function setComplaint(?Complaint $complaint): static
    {
        $this->complaint = $complaint;

        return $this;
    }
}
