<?php

namespace Crell\Bundle\Planedo\Controller\Admin;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    protected readonly FeedRepository $feedRepo;
    protected readonly FeedEntryRepository $entryRepo;

    public function __construct(
        protected readonly AdminUrlGenerator $routeBuilder,
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
            MenuItem::linkToCrud('Feeds', 'fa fa-list', Feed::class)
                ->setBadge($this->feedRepo->getActiveFeedCount()),
            MenuItem::linkToCrud('Feed Entries', 'fa fa-indent', FeedEntry::class)
                ->setBadge($this->entryRepo->getApprovedEntryCount()),

            MenuItem::section('Users'),
            MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
        ];
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            //->setName($user->getFullName())
            // use this method if you don't want to display the name of the user
            //->displayUserName(false)

            // you can return an URL with the avatar image
            //->setAvatarUrl('https://...')
            //->setAvatarUrl($user->getProfileImageUrl())
            // use this method if you don't want to display the user image
            //->displayUserAvatar(false)
            // you can also pass an email address to use gravatar's service
            //->setGravatarEmail($user->getMainEmailAddress())

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToRoute('Profile', 'fa fa-id-card', 'planedo_user_settings',),
            ]);
    }

}
