<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\DataFixtures;

use Crell\Bundle\Planedo\Entity\Feed;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FeedFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $feed = (new Feed())
            ->setTitle('Garfieldtech RSS')
            ->setActive(true)
            ->setFeedLink('https://www.garfieldtech.com/blog/feed');
        $manager->persist($feed);

        $feed = (new Feed())
            ->setTitle('PHP.net Atom')
            ->setActive(true)
            ->setFeedLink('https://www.php.net/feed.atom');
        $manager->persist($feed);

        $feed = (new Feed())
            ->setTitle('Fake Feed')
            ->setActive(true)
            ->setFeedLink('http://www.example.com/');
        $manager->persist($feed);

        $manager->flush();
    }
}
