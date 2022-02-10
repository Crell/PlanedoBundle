<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PlanedoBundle extends Bundle
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
