<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * This is only a local implementation to avoid dev package dependency complications.
 *
 * @todo Once PSR-20 passes, it's safe to replcae with a more stock implementation.
 */
class UtcClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
