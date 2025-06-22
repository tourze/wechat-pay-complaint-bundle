<?php

namespace WechatPayComplaintBundle\Tests\Integration\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;

#[ORM\Entity]
#[ORM\Table(name: 'test_merchant', options: ['comment' => '测试商户'])]
class TestMerchant implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(length: 32, options: ['comment' => '商户ID'])]
    private ?string $mchId = null;

    #[ORM\Column(length: 64, options: ['comment' => '商户名称'])]
    private ?string $name = null;

    #[ORM\Column(length: 64, nullable: true, options: ['comment' => 'API密钥'])]
    private ?string $apiKey = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '证书序列号'])]
    private ?string $serialNumber = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '私钥路径'])]
    private ?string $privateKeyPath = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '证书路径'])]
    private ?string $certificatePath = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMchId(): ?string
    {
        return $this->mchId;
    }

    public function setMchId(string $mchId): self
    {
        $this->mchId = $mchId;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function getPrivateKeyPath(): ?string
    {
        return $this->privateKeyPath;
    }

    public function setPrivateKeyPath(?string $privateKeyPath): self
    {
        $this->privateKeyPath = $privateKeyPath;
        return $this;
    }

    public function getCertificatePath(): ?string
    {
        return $this->certificatePath;
    }

    public function setCertificatePath(?string $certificatePath): self
    {
        $this->certificatePath = $certificatePath;
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
} 