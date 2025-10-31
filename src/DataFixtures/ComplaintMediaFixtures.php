<?php

namespace WechatPayComplaintBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatPayComplaintBundle\Entity\Complaint;
use WechatPayComplaintBundle\Entity\ComplaintMedia;

#[When(env: 'test')]
#[When(env: 'dev')]
class ComplaintMediaFixtures extends Fixture implements DependentFixtureInterface
{
    public const COMPLAINT_MEDIA_REFERENCE_PREFIX = 'complaint_media_';
    public const MEDIA_COUNT_PER_COMPLAINT = 3;

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    public function load(ObjectManager $manager): void
    {
        $mediaCount = 0;

        for ($i = 0; $i < ComplaintFixtures::COMPLAINT_COUNT; ++$i) {
            $complaint = $this->getReference(ComplaintFixtures::COMPLAINT_REFERENCE_PREFIX . $i, Complaint::class);

            $mediaItemsCount = $this->faker->numberBetween(1, self::MEDIA_COUNT_PER_COMPLAINT);

            for ($j = 0; $j < $mediaItemsCount; ++$j) {
                $media = $this->createComplaintMedia($complaint);
                $manager->persist($media);
                $this->addReference(self::COMPLAINT_MEDIA_REFERENCE_PREFIX . $mediaCount, $media);
                ++$mediaCount;
            }
        }

        $manager->flush();
    }

    private function createComplaintMedia(Complaint $complaint): ComplaintMedia
    {
        $media = new ComplaintMedia();

        $mediaTypes = ['image', 'video', 'audio', 'document'];
        $selectedType = $this->faker->randomElement($mediaTypes);
        $media->setMediaType(is_string($selectedType) ? $selectedType : 'image');

        $urlCount = $this->faker->numberBetween(1, 3);
        $urls = [];
        for ($i = 0; $i < $urlCount; ++$i) {
            $urls[] = $this->faker->imageUrl(640, 480, 'business', true);
        }
        $media->setMediaUrl($urls);

        $media->setComplaint($complaint);

        $createTime = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $media->setCreateTime(\DateTimeImmutable::createFromMutable($createTime));
        $media->setUpdateTime(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween($createTime, 'now')));

        return $media;
    }

    public function getDependencies(): array
    {
        return [
            ComplaintFixtures::class,
        ];
    }
}
