<?php

namespace WechatPayComplaintBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintRepository;

/**
 * 微信支付-投诉
 */
#[ORM\Entity(repositoryClass: ComplaintRepository::class)]
#[ORM\Table(name: 'wechat_payment_complaint', options: ['comment' => '微信支付-投诉'])]
class Complaint implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(targetEntity: Merchant::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Merchant $merchant = null;

    #[ORM\Column(length: 100, unique: true, options: ['comment' => '投诉单号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $wxComplaintId = null;

    #[ORM\Column(length: 100, options: ['comment' => '投诉时间'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $complaintTime = null;

    #[ORM\Column(length: 100, enumType: ComplaintState::class, options: ['comment' => '投诉单状态'])]
    #[Assert\NotNull]
    #[Assert\Choice(choices: ['PENDING', 'PROCESSING', 'PROCESSED'])]
    private ?ComplaintState $complaintState = null;

    #[ORM\Column(length: 11, nullable: true, options: ['comment' => '投诉人联系方式'])]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
    #[Assert\Length(max: 11)]
    private ?string $payerPhone = null;

    #[ORM\Column(length: 100, options: ['comment' => '本地订单号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $payOrderNo = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '微信订单号'])]
    #[Assert\Length(max: 100)]
    private ?string $wxPayOrderNo = null;

    #[ORM\Column(options: ['comment' => '订单金额'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $amount = null;

    #[ORM\Column(nullable: true, options: ['comment' => '申请退款金额'])]
    #[Assert\PositiveOrZero]
    private ?float $applyRefundAmount = null;

    #[ORM\Column(nullable: true, options: ['comment' => '投诉次数'])]
    #[Assert\PositiveOrZero]
    private ?int $userComplaintTimes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    #[Assert\Length(max: 65535)]
    private ?string $rawData = null;

    #[ORM\Column(length: 300, nullable: true, options: ['comment' => '投诉详情'])]
    #[Assert\Length(max: 300)]
    private ?string $complaintDetail = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '问题描述'])]
    #[Assert\Length(max: 255)]
    private ?string $problemDescription = null;

    #[ORM\Column(nullable: true, options: ['comment' => '投诉单是否已全额退款'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $complaintFullRefunded = null;

    /**
     * @var Collection<int, ComplaintMedia>
     */
    #[ORM\OneToMany(mappedBy: 'complaint', targetEntity: ComplaintMedia::class)]
    private Collection $complaintMedia;

    public function __construct()
    {
        $this->complaintMedia = new ArrayCollection();
    }

    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    public function setMerchant(?Merchant $merchant): void
    {
        $this->merchant = $merchant;
    }

    public function getWxComplaintId(): ?string
    {
        return $this->wxComplaintId;
    }

    public function setWxComplaintId(string $wxComplaintId): void
    {
        $this->wxComplaintId = $wxComplaintId;
    }

    public function getComplaintState(): ?ComplaintState
    {
        return $this->complaintState;
    }

    public function setComplaintState(?ComplaintState $complaintState): void
    {
        $this->complaintState = $complaintState;
    }

    public function getPayerPhone(): ?string
    {
        return $this->payerPhone;
    }

    public function setPayerPhone(?string $payerPhone): void
    {
        $this->payerPhone = $payerPhone;
    }

    public function getPayOrderNo(): ?string
    {
        return $this->payOrderNo;
    }

    public function setPayOrderNo(string $payOrderNo): void
    {
        $this->payOrderNo = $payOrderNo;
    }

    public function getWxPayOrderNo(): ?string
    {
        return $this->wxPayOrderNo;
    }

    public function setWxPayOrderNo(?string $wxPayOrderNo): void
    {
        $this->wxPayOrderNo = $wxPayOrderNo;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getUserComplaintTimes(): ?int
    {
        return $this->userComplaintTimes;
    }

    public function setUserComplaintTimes(?int $userComplaintTimes): void
    {
        $this->userComplaintTimes = $userComplaintTimes;
    }

    public function getApplyRefundAmount(): ?float
    {
        return $this->applyRefundAmount;
    }

    public function setApplyRefundAmount(?float $applyRefundAmount): void
    {
        $this->applyRefundAmount = $applyRefundAmount;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function getComplaintDetail(): ?string
    {
        return $this->complaintDetail;
    }

    public function setComplaintDetail(?string $complaintDetail): void
    {
        $this->complaintDetail = $complaintDetail;
    }

    public function isComplaintFullRefunded(): ?bool
    {
        return $this->complaintFullRefunded;
    }

    public function setComplaintFullRefunded(?bool $complaintFullRefunded): void
    {
        $this->complaintFullRefunded = $complaintFullRefunded;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProblemDescription(?string $problemDescription): void
    {
        $this->problemDescription = $problemDescription;
    }

    /**
     * @return Collection<int, ComplaintMedia>
     */
    public function getComplaintMedia(): Collection
    {
        return $this->complaintMedia;
    }

    public function addComplaintMedium(ComplaintMedia $complaintMedium): static
    {
        if (!$this->complaintMedia->contains($complaintMedium)) {
            $this->complaintMedia->add($complaintMedium);
            $complaintMedium->setComplaint($this);
        }

        return $this;
    }

    public function removeComplaintMedium(ComplaintMedia $complaintMedium): static
    {
        if ($this->complaintMedia->removeElement($complaintMedium)) {
            // set the owning side to null (unless already changed)
            if ($complaintMedium->getComplaint() === $this) {
                $complaintMedium->setComplaint(null);
            }
        }

        return $this;
    }

    public function getComplaintTime(): ?string
    {
        return $this->complaintTime;
    }

    public function setComplaintTime(string $complaintTime): void
    {
        $this->complaintTime = $complaintTime;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
