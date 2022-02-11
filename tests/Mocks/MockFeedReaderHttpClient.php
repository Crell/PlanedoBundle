<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Mocks;

use Laminas\Feed\Reader\Http\ClientInterface;
use Laminas\Feed\Reader\Http\ResponseInterface;

class MockFeedReaderHttpClient implements ClientInterface
{
    public function __construct(
        /** URL -> file name. */
        protected array $map,
    ) {
    }

    /**
     * @param string $uri
     */
    public function get($uri): ResponseInterface
    {
        $file = $this->map[$uri];

        if (!file_exists($file)) {
            throw new \InvalidArgumentException('No fixture feed found: ' . $file);
        }

        $body = file_get_contents($file);

        return new MockResponse(200, $body);
    }
}
