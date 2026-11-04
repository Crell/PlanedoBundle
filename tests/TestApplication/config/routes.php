<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // EasyAdmin 5 generates the dashboard, CRUD and #[AdminRoute] routes
    // through its own route loader; this import is mandatory.
    $routes->import('.', 'easyadmin.routes');

    $routes->import('@PlanedoBundle/config/routes_admin.yaml')->prefix('/');
    $routes->import('@PlanedoBundle/config/routes_public.yaml')->prefix('/');
};
