<?php

namespace WechatPayComplaintBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatPayComplaintBundle\Repository\ComplaintReplyRepository;

/**
 * 微信支付-投诉回复
 */
#[ORM\Entity(repositoryClass: ComplaintReplyRepository::class)]
#[ORM\Table(name: 'wechat_payment_complaint_reply', options: ['comment' => '微信支付-投诉回复'])]
class ComplaintReply implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\Column(length: 255, options: ['comment' => '回复内容'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $content = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'complaintReply')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Complaint $complaint = null;

    public function getComplaint(): ?Complaint
    {
        return $this->complaint;
    }

    public function setComplaint(?Complaint $complaint): void
    {
        $this->complaint = $complaint;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
