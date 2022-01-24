<?php

namespace Crell\Bundle\Planedo\Controller;

use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserSettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {}

    #[Route('/admin/user-settings', name: 'user_settings')]
    public function index(#[CurrentUser] $user, Request $request): Response
    {
        $form = $this->createForm(UserSettingsType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $updatedUser = $form->getData();
            $this->em->persist($updatedUser);
            $this->em->flush();

            $this->addFlash('success', 'User information updated.');

            $url = $this->adminUrlGenerator
                ->setRoute('user_settings')
                ->generateUrl();

            return $this->redirect($url);
        }

        return $this->renderForm('user_settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
