<?php

namespace WechatPayComplaintBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Enum\ComplaintState;
use WechatPayComplaintBundle\Repository\ComplaintRepository;

/**
 * 微信支付-投诉
 */
#[ORM\Entity(repositoryClass: ComplaintRepository::class)]
#[ORM\Table(name: 'wechat_payment_complaint', options: ['comment' => '微信支付-投诉'])]
class Complaint
{
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Merchant $merchant = null;

    #[ListColumn]
    #[ORM\Column(length: 100, unique: true, options: ['comment' => '投诉单号'])]
    private ?string $wxComplaintId = null;

    #[ORM\Column(length: 100, options: ['comment' => '投诉时间'])]
    private ?string $complaintTime = null;

    #[ListColumn]
    #[ORM\Column(length: 100, enumType: ComplaintState::class, options: ['comment' => '投诉单状态'])]
    private ?ComplaintState $complaintState = null;

    #[ListColumn]
    #[ORM\Column(length: 11, nullable: true, options: ['comment' => '投诉人联系方式'])]
    private ?string $payerPhone = null;

    #[ListColumn]
    #[ORM\Column(length: 100, options: ['comment' => '本地订单号'])]
    private ?string $payOrderNo = null;

    #[ListColumn]
    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '微信订单号'])]
    private ?string $wxPayOrderNo = null;

    #[ListColumn]
    #[ORM\Column(options: ['comment' => '订单金额'])]
    private ?float $amount = null;

    #[ListColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '申请退款金额'])]
    private ?float $applyRefundAmount = null;

    #[ListColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '投诉次数'])]
    private ?int $userComplaintTimes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    private ?string $rawData = null;

    #[ListColumn]
    #[ORM\Column(length: 300, nullable: true, options: ['comment' => '投诉详情'])]
    private ?string $complaintDetail = null;

    #[ListColumn]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '问题描述'])]
    private ?string $problemDescription = null;

    #[ListColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '投诉单是否已全额退款'])]
    private ?bool $complaintFullRefunded = null;

    #[ORM\OneToMany(mappedBy: 'complaint', targetEntity: ComplaintMedia::class)]
    private Collection $complaintMedia;

    public function __construct()
    {
        $this->complaintMedia = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    public function setMerchant(?Merchant $merchant): static
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function getWxComplaintId(): ?string
    {
        return $this->wxComplaintId;
    }

    public function setWxComplaintId(string $wxComplaintId): static
    {
        $this->wxComplaintId = $wxComplaintId;

        return $this;
    }

    public function getComplaintState(): ?ComplaintState
    {
        return $this->complaintState;
    }

    public function setComplaintState(ComplaintState $complaintState): static
    {
        $this->complaintState = $complaintState;

        return $this;
    }

    public function getPayerPhone(): ?string
    {
        return $this->payerPhone;
    }

    public function setPayerPhone(?string $payerPhone): static
    {
        $this->payerPhone = $payerPhone;

        return $this;
    }

    public function getPayOrderNo(): ?string
    {
        return $this->payOrderNo;
    }

    public function setPayOrderNo(string $payOrderNo): static
    {
        $this->payOrderNo = $payOrderNo;

        return $this;
    }

    public function getWxPayOrderNo(): ?string
    {
        return $this->wxPayOrderNo;
    }

    public function setWxPayOrderNo(?string $wxPayOrderNo): static
    {
        $this->wxPayOrderNo = $wxPayOrderNo;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getUserComplaintTimes(): ?int
    {
        return $this->userComplaintTimes;
    }

    public function setUserComplaintTimes(?int $userComplaintTimes): static
    {
        $this->userComplaintTimes = $userComplaintTimes;

        return $this;
    }

    public function getApplyRefundAmount(): ?float
    {
        return $this->applyRefundAmount;
    }

    public function setApplyRefundAmount(?float $applyRefundAmount): static
    {
        $this->applyRefundAmount = $applyRefundAmount;

        return $this;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): static
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getComplaintDetail(): ?string
    {
        return $this->complaintDetail;
    }

    public function setComplaintDetail(?string $complaintDetail): static
    {
        $this->complaintDetail = $complaintDetail;

        return $this;
    }

    public function isComplaintFullRefunded(): ?bool
    {
        return $this->complaintFullRefunded;
    }

    public function setComplaintFullRefunded(?bool $complaintFullRefunded): static
    {
        $this->complaintFullRefunded = $complaintFullRefunded;

        return $this;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProblemDescription(?string $problemDescription): static
    {
        $this->problemDescription = $problemDescription;

        return $this;
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

    public function setComplaintTime(string $complaintTime): static
    {
        $this->complaintTime = $complaintTime;

        return $this;
    }
}
