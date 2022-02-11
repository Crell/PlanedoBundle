<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Controller;

use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HtmlFeedController extends AbstractController
{
    public function __construct(
        protected FeedEntryRepository $repository,
        protected int $itemsPerPage,
    ) {
    }

    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->repository->latestEntriesPaginator($offset);

        return $this->render('@Planedo/html_feed/index.html.twig', [
            'controller_name' => 'HtmlFeedController',
            'entries' => $paginator,
            'previous' => $offset - $this->itemsPerPage,
            'next' => min(count($paginator), $offset + $this->itemsPerPage),
        ]);
    }
}
