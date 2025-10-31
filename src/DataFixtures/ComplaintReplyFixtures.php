<?php

namespace WechatPayComplaintBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintReply;

#[When(env: 'test')]
#[When(env: 'dev')]
class ComplaintReplyFixtures extends Fixture implements DependentFixtureInterface
{
    public const COMPLAINT_REPLY_REFERENCE_PREFIX = 'complaint_reply_';
    public const REPLY_COUNT_PER_COMPLAINT = 2;

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    public function load(ObjectManager $manager): void
    {
        $replyCount = 0;

        for ($i = 0; $i < ComplaintFixtures::COMPLAINT_COUNT; ++$i) {
            $complaint = $this->getReference(ComplaintFixtures::COMPLAINT_REFERENCE_PREFIX . $i, Complaint::class);

            $replyItemsCount = $this->faker->numberBetween(0, self::REPLY_COUNT_PER_COMPLAINT);

            for ($j = 0; $j < $replyItemsCount; ++$j) {
                $reply = $this->createComplaintReply($complaint);
                $manager->persist($reply);
                $this->addReference(self::COMPLAINT_REPLY_REFERENCE_PREFIX . $replyCount, $reply);
                ++$replyCount;
            }
        }

        $manager->flush();
    }

    private function createComplaintReply(Complaint $complaint): ComplaintReply
    {
        $reply = new ComplaintReply();

        $replyTemplates = [
            '感谢您的反馈，我们已经收到您的投诉，正在处理中。',
            '经核实，我们将在2个工作日内为您处理退款。',
            '关于您提到的问题，我们已安排专人跟进处理。',
            '抱歉给您带来不便，我们会尽快解决您的问题。',
            '您的投诉我们已转交相关部门处理，请耐心等待。',
        ];

        $selectedContent = $this->faker->randomElement($replyTemplates);
        $reply->setContent(is_string($selectedContent) ? $selectedContent : null);
        $reply->setComplaint($complaint);

        $createTime = $this->faker->dateTimeBetween('-20 days', '-1 day');
        $reply->setCreateTime(\DateTimeImmutable::createFromMutable($createTime));
        $reply->setUpdateTime(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween($createTime, 'now')));

        return $reply;
    }

    public function getDependencies(): array
    {
        return [
            ComplaintFixtures::class,
        ];
    }
}
