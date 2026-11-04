<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Controller\Admin;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The EasyAdmin dashboard.
 *
 * EasyAdmin 5 generates the dashboard route (and all CRUD routes below it)
 * from this attribute. Applications must import the "easyadmin.routes"
 * route loader for these routes to exist; see the README.
 */
#[AdminDashboard(routePath: '/admin', routeName: 'planedo_admin')]
class DashboardController extends AbstractDashboardController
{
    protected readonly FeedRepository $feedRepo;
    protected readonly FeedEntryRepository $entryRepo;

    public function __construct(
        protected readonly AdminUrlGeneratorInterface $routeBuilder,
        EntityManagerInterface $em,
    ) {
        $this->feedRepo = $em->getRepository(Feed::class);
        $this->entryRepo = $em->getRepository(FeedEntry::class);
    }

    public function index(): Response
    {
        return $this->redirect($this->routeBuilder->setController(FeedCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Planedo');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Feeds'),
            MenuItem::linkTo(FeedCrudController::class, 'Feeds', 'fa fa-list')
                ->setBadge($this->feedRepo->getActiveFeedCount()),
            MenuItem::linkTo(FeedEntryCrudController::class, 'Feed Entries', 'fa fa-indent')
                ->setBadge($this->entryRepo->getApprovedEntryCount()),

            MenuItem::section('Users'),
            MenuItem::linkTo(UserCrudController::class, 'Users', 'fa fa-user'),
        ];
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Calling the parent method gives us a user menu with the standard
        // items already created ("sign out", "exit impersonation", etc.).
        return parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToRoute('Profile', 'fa fa-id-card', 'planedo_admin_user_settings'),
            ]);
    }
}
