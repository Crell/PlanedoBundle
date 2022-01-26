<?php

namespace Crell\Bundle\Planedo\Controller;

use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HtmlFeedController extends AbstractController
{
    public function __construct(
        protected FeedEntryRepository $repository,
        protected int $itemsPerPage,
    ) {}

    #[Route('/', name: 'html_main')]
    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->repository->latestEntriesPaginator($offset);

        return $this->render('@CrellPlanedo/html_feed/index.html.twig', [
            'controller_name' => 'HtmlFeedController',
            'entries' => $paginator,
            'previous' => $offset - $this->itemsPerPage,
            'next' => min(count($paginator), $offset + $this->itemsPerPage),
        ]);
    }
}
