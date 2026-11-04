<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Controller\Admin;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Message\UpdateFeed;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractCrudController<Feed>
 */
class FeedCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly AdminUrlGeneratorInterface $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Feed')
            ->setEntityLabelInPlural('Feeds')
            ->setPaginatorPageSize(50)
            ->setDefaultSort(['title' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyWhenUpdating()->setDisabled(),
            TextField::new('title'),
            TextField::new('feedLink', 'Feed URL'),
            BooleanField::new('active', 'Active'),
            DateTimeField::new('lastUpdated')->hideWhenCreating()->setDisabled(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_EDIT, Action::DELETE);

        $updateFeed = Action::new('updateFeed', 'Update')
            ->renderAsLink()
            ->linkToCrudAction('updateFeed');

        $actions->add(Crud::PAGE_EDIT, $updateFeed);
        $actions->add(Crud::PAGE_INDEX, $updateFeed);

        $updateFeeds = Action::new('updateFeeds', 'Update')
            ->linkToCrudAction('batchUpdateFeeds')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-check')
        ;

        $actions->addBatchAction($updateFeeds);

        return $actions;
    }

    #[AdminRoute('/batch-update', name: 'batch_update', options: ['methods' => ['POST']])]
    public function batchUpdateFeeds(BatchActionDto $batchActionDto, Request $request): Response
    {
        $this->assertBatchActionIsValid($batchActionDto);

        $ids = $batchActionDto->getEntityIds();
        foreach ($ids as $id) {
            $this->bus->dispatch(new UpdateFeed((int) $id));
        }

        $this->addFlash('notice', sprintf('Update queued for %d feeds.', count($ids)));

        return $this->redirect($this->referrerOrIndex($request));
    }

    #[AdminRoute('/{entityId}/update', name: 'update')]
    public function updateFeed(AdminContext $context): Response
    {
        /** @var Feed $feed */
        $feed = $context->getEntity()->getInstance();

        if (!$feed->isActive()) {
            $this->addFlash('warning', sprintf('Feed %s (%d) is inactive, so will not be updated.', $feed->getTitle(), $feed->getId()));

            return $this->redirect($this->referrerOrIndex($context->getRequest()));
        }

        $this->bus->dispatch(new UpdateFeed($feed->getId()));

        $this->addFlash('notice', sprintf('Feed %s (%d) queued for updating', $feed->getTitle(), $feed->getId()));

        return $this->redirect($this->referrerOrIndex($context->getRequest()));
    }

    /**
     * EasyAdmin 5 no longer tracks the referrer itself, so fall back to the
     * browser-provided header and, failing that, this controller's index page.
     */
    private function referrerOrIndex(Request $request): string
    {
        return $request->headers->get('referer')
            ?? $this->adminUrlGenerator->setController(static::class)->setAction(Action::INDEX)->generateUrl();
    }

    private function assertBatchActionIsValid(BatchActionDto $batchActionDto): void
    {
        if (static::getEntityFqcn() !== $batchActionDto->getEntityFqcn()) {
            throw $this->createAccessDeniedException();
        }

        $tokenId = 'ea-batch-action-' . $batchActionDto->getName() . '-' . $batchActionDto->getEntityFqcn();
        if (!$this->isCsrfTokenValid($tokenId, $batchActionDto->getCsrfToken())) {
            throw $this->createAccessDeniedException();
        }
    }
}
