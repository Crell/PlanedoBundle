<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo;

use Laminas\Feed\Reader\Http\ClientInterface as FeedReaderHttpClientInterface;
use Laminas\Feed\Reader\Http\Psr7ResponseDecorator;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\Psr18Client;

class FeedReaderClient implements FeedReaderHttpClientInterface
{
    private ClientInterface $client;

    public function __construct(?Psr18Client $psrClient = null)
    {
        $this->client = $psrClient ?? new Psr18Client();
    }

    public function get($uri): Psr7ResponseDecorator
    {
        $request = $this->client->createRequest('GET', $uri);

        return new Psr7ResponseDecorator(
            $this->client->sendRequest($request)
        );
    }
}
