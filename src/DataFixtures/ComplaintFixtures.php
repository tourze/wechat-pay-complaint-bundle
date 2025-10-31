<?php

namespace WechatPayComplaintBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatPayBundle\DataFixtures\MerchantFixtures;
use WechatPayBundle\Entity\Merchant;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Enum\ComplaintState;

#[When(env: 'test')]
#[When(env: 'dev')]
class ComplaintFixtures extends Fixture implements DependentFixtureInterface
{
    public const COMPLAINT_REFERENCE_PREFIX = 'complaint_';
    public const COMPLAINT_COUNT = 10;

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    public function getDependencies(): array
    {
        return [
            MerchantFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::COMPLAINT_COUNT; ++$i) {
            $complaint = $this->createComplaint();
            $manager->persist($complaint);
            $this->addReference(self::COMPLAINT_REFERENCE_PREFIX . $i, $complaint);
        }

        $manager->flush();
    }

    private function createComplaint(): Complaint
    {
        $complaint = new Complaint();

        $testMerchant = $this->getReference(MerchantFixtures::TEST_MERCHANT_REFERENCE, Merchant::class);
        $complaint->setMerchant($testMerchant);

        $complaint->setWxComplaintId('C' . $this->faker->unique()->randomNumber(8));
        $complaint->setComplaintTime($this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'));
        $complaintStateCase = $this->faker->randomElement(ComplaintState::cases());
        $complaint->setComplaintState($complaintStateCase instanceof ComplaintState ? $complaintStateCase : ComplaintState::PENDING);
        $complaint->setPayerPhone($this->faker->phoneNumber());
        $complaint->setPayOrderNo('PO' . $this->faker->unique()->randomNumber(8));
        $complaint->setWxPayOrderNo($this->faker->optional()->regexify('[0-9]{28}'));
        $complaint->setAmount($this->faker->randomFloat(2, 1, 10000));
        $complaint->setApplyRefundAmount($this->faker->optional()->randomFloat(2, 0, 5000));
        $complaint->setUserComplaintTimes($this->faker->optional()->numberBetween(1, 5));
        $complaint->setRawData($this->faker->optional()->text(200));
        $complaint->setComplaintDetail($this->faker->optional()->sentence(20));
        $complaint->setProblemDescription($this->faker->optional()->sentence(10));
        $complaint->setComplaintFullRefunded($this->faker->optional()->boolean());

        $createTime = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $complaint->setCreateTime(\DateTimeImmutable::createFromMutable($createTime));
        $complaint->setUpdateTime(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween($createTime, 'now')));

        return $complaint;
    }
}
