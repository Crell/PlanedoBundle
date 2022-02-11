<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CrellPlanedoBundle extends Bundle
{
    /**
     * Override the base path so that we can put files in the package root.
     *
     * Bundle's default forces everything into `src`, even though the recommended
     * structure is to NOT do that.  This is... broken in Symfony.
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
