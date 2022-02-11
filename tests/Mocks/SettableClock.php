<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Mocks;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class SettableClock implements ClockInterface
{
    public function __construct(protected ?DateTimeImmutable $now = null)
    {
        $this->now ??= new DateTimeImmutable();
    }

    public function set(DateTimeImmutable $time): static
    {
        $this->now = $time;

        return $this;
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
