<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\DataFixtures;

use Crell\Bundle\Planedo\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class FeedFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['tests', 'manual'];
    }

    public function load(ObjectManager $manager): void
    {
        $feed = new Feed();
        $feed
            ->setTitle('Garfieldtech RSS')
            ->setActive(true)
            ->setFeedLink('https://www.garfieldtech.com/blog/feed');
        $manager->persist($feed);

        $feed = new Feed();
        $feed
            ->setTitle('PHP.net Atom')
            ->setActive(true)
            ->setFeedLink('https://www.php.net/feed.atom');
        $manager->persist($feed);

        $manager->flush();
    }
}
