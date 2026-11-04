<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional\Controller\Admin;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Crell\Bundle\Planedo\Tests\Functional\EntityManagerWrapper;
use Crell\Bundle\Planedo\Tests\Functional\SetupUtils;
use Crell\Bundle\Planedo\Tests\Functional\UserUtils;
use Crell\Bundle\Planedo\Tests\Functional\WebTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('admin')]
class AdminTest extends WebTestCase
{
    use UserUtils;
    use EntityManagerWrapper;
    use SetupUtils;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addFixture(new FeedFixtures());
        $this->addFixture(new FeedTestFixtures());
        $this->executeFixtures();
    }

    #[Test]
    public function anonUserGetsLoginFormOnAdmin(): void
    {
        // Don't login first!

        $this->client->request('GET', '/admin');
        $crawler = $this->client->followRedirect();

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[action=""]');

        self::assertEquals('Planedo login', $crawler->filter('title')->text());

        self::assertCount(1, $form);
    }

    #[Test]
    public function feedIndexLoads(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/admin');

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $crawler = $this->client->clickLink('Feeds');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feeds', $crawler->filter('title')->text());
    }

    #[Test]
    public function feedEntryIndexLoads(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // It is not at all clear why we need to send a dummy request
        // before logging in.  If we don't, the loginUser() call fails with
        // a message that we cannot get a container from a non-booted kernel,
        // yet the kernel is definitely booted by this point.
        $this->client->request('GET', '/admin');

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/admin');
        $this->client->followRedirect();

        $crawler = $this->client->clickLink('Feed Entries');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feed entries', $crawler->filter('title')->text());
    }

    #[Test]
    public function feedEntryDetailLoadsForUrlIdentifiers(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $this->client->request('GET', '/admin');
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $crawler = $this->client->request('GET', '/admin/feed-entry');
        self::assertResponseIsSuccessful();

        // Feed entries use their own URL as identifier, which EasyAdmin 5 puts
        // into the route path. Make sure such links can be generated and resolved.
        $link = $crawler->filter('a.action-detail')->first()->link();
        $path = rawurldecode(parse_url($link->getUri(), PHP_URL_PATH));
        $entryId = substr($path, strlen('/admin/feed-entry/'));
        self::assertStringStartsWith('http', $entryId);

        /** @var FeedEntry $entry */
        $entry = $this->feedEntryRepo()->find($entryId);
        self::assertNotNull($entry);

        $crawler = $this->client->click($link);
        self::assertResponseIsSuccessful();
        self::assertEquals($entry->getTitle(), $crawler->filter('title')->text());
    }

    #[Test]
    public function rejectEntryActionRejectsTheEntry(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        $this->client->request('GET', '/admin');
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $crawler = $this->client->request('GET', '/admin/feed-entry');
        self::assertResponseIsSuccessful();

        $link = $crawler->filter('a.action-rejectEntry')->first()->link();
        parse_str((string) parse_url($link->getUri(), PHP_URL_QUERY), $query);
        $entryId = $query['entityId'];
        self::assertTrue($this->feedEntryRepo()->find($entryId)->isApproved());

        $this->client->click($link);
        self::assertResponseRedirects('/admin/feed-entry');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Rejected entry');

        // The handler updates via DQL, so bypass the identity map.
        $this->entityManager()->clear();
        self::assertFalse($this->feedEntryRepo()->find($entryId)->isApproved());
    }

    #[Test]
    public function updateFeedActionResolvesTheFeed(): void
    {
        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();

        // Deactivate every feed so the action takes its "inactive" branch and
        // does not try to fetch anything over the network.
        $em = $this->entityManager();
        foreach ($this->feedRepo()->findAll() as $feed) {
            $feed->setActive(false);
            $em->persist($feed);
        }
        $em->flush();

        $this->client->request('GET', '/admin');
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $this->client->loginUser($adminUser);

        $crawler = $this->client->request('GET', '/admin/feed');
        self::assertResponseIsSuccessful();

        $link = $crawler->filter('a.action-updateFeed')->first()->link();
        self::assertMatchesRegularExpression('#^http://localhost/admin/feed/\d+/update$#', $link->getUri());

        $this->client->click($link);
        self::assertResponseRedirects('/admin/feed');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'is inactive, so will not be updated');
    }
}
