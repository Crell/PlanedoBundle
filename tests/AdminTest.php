<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group admin
 */
class AdminTest extends WebTestCase
{
    use UserUtils;
    use SetupUtils;

    protected static function container(): ContainerInterface
    {
        // TODO: Implement container() method.
    }


    /**
     * @test
     */
    public function anon_user_gets_login_form_on_admin(): void
    {
        $client = static::createClient();

        // Don't login.

        $client->request('GET', '/admin');
        $crawler = $client->followRedirect();

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[action=""]');

        self::assertEquals('Planedo login', $crawler->filter('title')->text());

        self::assertCount(1, $form);
    }

    /**
     * @test
     */
    public function feed_index_loads(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $crawler = $client->clickLink('Feeds');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feeds', $crawler->filter('title')->text());
    }

    /**
     * @test
     */
    public function feed_entry_index_loads(): void
    {
        $client = static::createClient();

        $this->mockClock(new \DateTimeImmutable('02 Dec 2021 01:01:01 +0000'));
        $this->mockFeedClient();
        $this->populateFeeds();

        // Login.
        $adminUser = $this->createUser('me@me.com', 'asdf');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin');
        $client->followRedirect();

        $crawler = $client->clickLink('Feed Entries');

        self::assertResponseIsSuccessful();
        self::assertEquals('Feed entries', $crawler->filter('title')->text());
    }
}
