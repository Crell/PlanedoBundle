<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
