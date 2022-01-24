<?php

namespace Crell\Bundle\Planedo\DataFixtures;

use Crell\Bundle\Planedo\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class FeedTestFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['tests'];
    }

    public function load(ObjectManager $manager): void
    {
        $feed = new Feed();
        $feed
            ->setTitle('Fake Feed')
            ->setActive(true)
            ->setFeedLink('http://www.example.com/');
        $manager->persist($feed);

        $manager->flush();
    }
}
