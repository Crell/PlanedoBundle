<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo;

use GuzzleHttp\Client;
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