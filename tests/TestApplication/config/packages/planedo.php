<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/*
 * Non-default configuration to use for tests.
 *
 * It would be better to set these in the relevant tests, but
 * it's non-obvious how to do that.  The best we can do is replicate
 * what a full on application would let you do.
 */

$container->loadFromExtension('planedo', [
    'items_per_page' => 5,
    'purge_before' => '-10 days',
]);
