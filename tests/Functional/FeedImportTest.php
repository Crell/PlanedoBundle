<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\DataFixtures\FeedFixtures;
use Crell\Bundle\Planedo\Tests\Functional\DataFixtures\FeedTestFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FeedImportTest extends KernelTestCase
{
    use DatabasePrimer;
    use DatabaseFixtures;
    use MockClock;
    use MockFeedReaderClient;

    use SetupUtils;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->prime();
        $this->addFixture(new FeedFixtures());
        $this->addFixture(new FeedTestFixtures());
        $this->executeFixtures();
    }

    /**
     * @test
     */
    public function stuff(): void
    {
        $this->mockClock(new \DateTimeImmutable('2021-11-15'));
        $this->mockFeedClient();

        $this->populateFeeds();

       $this->assertRawEntryCount(11);
    }
}
