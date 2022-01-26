<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests\Mocks;

use Laminas\Feed\Reader\Http\ClientInterface;
use Laminas\Feed\Reader\Http\ResponseInterface;

class MockFeedReaderHttpClient implements ClientInterface
{
    public function __construct(
        /** URL -> file name. */
        protected array $map,
    ){}

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

