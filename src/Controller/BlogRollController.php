<?php

namespace Crell\Bundle\Planedo\Controller;

use Crell\Bundle\Planedo\Repository\FeedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogRollController extends AbstractController
{
    public function __construct(
        protected FeedRepository $repository,
        protected int $itemsPerPage,
    ) {}

    #[Route('/blogroll', name: 'blog_roll')]
    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));

        $feeds = $this->repository->paginatedByName($offset);

        return $this->render('blog_roll/index.html.twig', [
            'controller_name' => 'BlogRollController',
            'feeds' => $feeds,
            'previous' => $offset - $this->itemsPerPage,
            'next' => min(count($feeds), $offset + $this->itemsPerPage),
        ]);
    }

    // Route deliberately excluded. This is just for partial inclusion.
    public function mostActive(int $max = 5): Response
    {
        $feeds = $this->repository->getMostActive($max);

        return $this->render('blog_roll/_most_active.html.twig', [
            'controller_name' => 'BlogRollController',
            'feeds' => $feeds,
        ]);
    }
}
