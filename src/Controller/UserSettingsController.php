<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Controller;

use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Lets the logged-in user edit their own account.
 *
 * This is a plain Symfony controller integrated into the EasyAdmin backend
 * via the #[AdminRoute] attribute, so EasyAdmin generates the route
 * "planedo_admin_user_settings" at "/admin/user-settings" (relative to the
 * dashboard route) and renders it inside the admin layout.
 */
class UserSettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[AdminRoute('/user-settings', name: 'user_settings')]
    public function index(#[CurrentUser] User $user, Request $request): Response
    {
        $form = $this->createForm(UserSettingsType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $updatedUser = $form->getData();
            $this->em->persist($updatedUser);
            $this->em->flush();

            $this->addFlash('success', 'User information updated.');

            return $this->redirectToRoute('planedo_admin_user_settings');
        }

        return $this->render('@Planedo/user_settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
