<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->import('@PlanedoBundle/Resources/config/routes_admin.yaml')->prefix('/');
    $routes->import('@PlanedoBundle/Resources/config/routes_public.yaml')->prefix('/');
};
