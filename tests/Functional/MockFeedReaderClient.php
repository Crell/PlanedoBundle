<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\Tests\Mocks\MockFeedReaderHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;

trait MockFeedReaderClient
{
    public function mockFeedClient()
    {
        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;

        $mockClient = new MockFeedReaderHttpClient([
            'https://www.garfieldtech.com/blog/feed' => 'tests/feed-data/garfieldtech.rss',
            'http://www.planet-php.org/rss/' => 'tests/feed-data/planetphp.092.rss',
            'http://www.planet-php.org/rdf/' => 'tests/feed-data/planetphp.10.xml',
            'https://www.php.net/feed.atom' => 'tests/feed-data/phpnet.atom',
            'http://www.example.com/' => 'tests/feed-data/fake1.rss',
        ]);

        $kernel->getContainer()->set('planedo.client.feedreader', $mockClient);
    }
}
