<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\Tests\Mocks\SettableClock;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait MockClockTrait
{
    public function mockClock(DateTimeImmutable $datetime): ClockInterface
    {
        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;

        $clock = new SettableClock($datetime);
        $kernel->getContainer()->set('planedo.clock', $clock);

        return $clock;
    }
}
