<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo;

use Laminas\Feed\Reader\Feed;
use Laminas\Feed\Reader\Http;
use Laminas\Feed\Reader\Reader;

/**
 * Wrapper class around Laminas Feed Reader.
 *
 * Feed Reader uses a bunch of static methods that make mocking impossible.
 * This class provides a workaround.
 */
class FeedReader
{
    public function __construct(private Http\ClientInterface $client)
    {
        Reader::setHttpClient($this->client);
    }

    public function import(string $uri, ?string $etag = null, string $lastModified = null): Feed\FeedInterface
    {
        return Reader::import($uri, $etag, $lastModified);
    }

    public static function importRemoteFeed($uri, Http\ClientInterface $client): Feed\FeedInterface
    {
        return Reader::importRemoteFeed($uri, $client);
    }

    public static function importString(string $string): Feed\FeedInterface
    {
        return Reader::importString($string);
    }

    public static function importFile(string $filename): Feed\FeedInterface
    {
        return Reader::importFile($filename);
    }


}