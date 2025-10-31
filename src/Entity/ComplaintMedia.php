<?php

namespace WechatPayComplaintBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatPayComplaintBundle\Repository\ComplaintMediaRepository;

/**
 * 微信支付-投诉资料
 */
#[ORM\Entity(repositoryClass: ComplaintMediaRepository::class)]
#[ORM\Table(name: 'wechat_payment_complaint_media', options: ['comment' => '微信支付-投诉资料'])]
class ComplaintMedia implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\Column(length: 100, options: ['comment' => '媒体类型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $mediaType = null;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '媒体URL列表'])]
    #[Assert\NotNull]
    private array $mediaUrl = [];

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'complaintMedia')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Complaint $complaint = null;

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    /**
     * @return array<int, string>
     */
    public function getMediaUrl(): array
    {
        return $this->mediaUrl;
    }

    /**
     * @param array<int, string> $mediaUrl
     */
    public function setMediaUrl(array $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    public function getComplaint(): ?Complaint
    {
        return $this->complaint;
    }

    public function setComplaint(?Complaint $complaint): void
    {
        $this->complaint = $complaint;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
