<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Controller\Admin;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\ApproveEntries;
use Crell\Bundle\Planedo\Message\RejectEntries;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Feed entries are identified by their own URL, which contains slashes.
 * EasyAdmin 5 puts entity ids into the URL path with a "no slashes" requirement,
 * so the single-entity actions below either carry the id in the query string
 * (approve/reject) or relax the requirement (detail).
 *
 * @extends AbstractCrudController<FeedEntry>
 */
class FeedEntryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly AdminUrlGeneratorInterface $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return FeedEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Feed entry')
            ->setEntityLabelInPlural('Feed entries')
            ->setPaginatorPageSize(50)
            ->setDefaultSort(['dateModified' => 'DESC'])
            ->setPageTitle(Crud::PAGE_DETAIL, static fn (FeedEntry $entry) => $entry->getTitle())
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')->setDisabled(),
            TextField::new('link')->setDisabled(),
            DateTimeField::new('dateModified', 'Date')->setDisabled(),
            BooleanField::new('approved', 'Approved')->setDisabled(),
            // @todo This contains HTML, so figure out how to format nicely.
            TextField::new('summary')->setDisabled()->onlyOnDetail(),
            TextField::new('feed.title', 'Feed')->setDisabled()->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $rejectEntry = Action::new('rejectEntry', 'Reject')
            ->renderAsLink()
            ->linkToCrudAction('rejectEntry')
            ->displayIf(static fn (FeedEntry $entry): bool => $entry->isApproved());

        $approveEntry = Action::new('approveEntry', 'Approve')
            ->renderAsLink()
            ->linkToCrudAction('approveEntry')
            ->displayIf(static fn (FeedEntry $entry): bool => !$entry->isApproved());

        $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $rejectEntry)
            ->add(Crud::PAGE_INDEX, $approveEntry)
            ->add(Crud::PAGE_DETAIL, $rejectEntry)
            ->add(Crud::PAGE_DETAIL, $approveEntry)
        ;

        $rejectEntries = Action::new('rejectEntries', 'Reject')
            ->linkToCrudAction('batchRejectEntries')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-check')
        ;
        $restoreEntries = Action::new('approveEntries', 'Approve')
            ->linkToCrudAction('batchApproveEntries')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-check')
        ;

        $actions->addBatchAction($rejectEntries);
        $actions->addBatchAction($restoreEntries);

        return $actions;
    }

    #[AdminRoute('/batch-reject', name: 'batch_reject', options: ['methods' => ['POST']])]
    public function batchRejectEntries(BatchActionDto $batchActionDto, Request $request): Response
    {
        $this->assertBatchActionIsValid($batchActionDto);

        $ids = array_map(strval(...), $batchActionDto->getEntityIds());
        $this->bus->dispatch(new RejectEntries(...$ids));

        $this->addFlash('notice', sprintf('%d entries rejected.', count($ids)));

        return $this->redirect($this->referrerOrIndex($request));
    }

    #[AdminRoute('/reject', name: 'reject')]
    public function rejectEntry(AdminContext $context): Response
    {
        /** @var FeedEntry $entry */
        $entry = $context->getEntity()->getInstance();

        $this->bus->dispatch(new RejectEntries($entry->getId()));

        $this->addFlash('notice', sprintf('Rejected entry: %s', $entry->getTitle()));

        return $this->redirect($this->referrerOrIndex($context->getRequest()));
    }

    #[AdminRoute('/batch-approve', name: 'batch_approve', options: ['methods' => ['POST']])]
    public function batchApproveEntries(BatchActionDto $batchActionDto, Request $request): Response
    {
        $this->assertBatchActionIsValid($batchActionDto);

        $ids = array_map(strval(...), $batchActionDto->getEntityIds());
        $this->bus->dispatch(new ApproveEntries(...$ids));

        $this->addFlash('notice', sprintf('%d entries approved.', count($ids)));

        return $this->redirect($this->referrerOrIndex($request));
    }

    #[AdminRoute('/approve', name: 'approve')]
    public function approveEntry(AdminContext $context): Response
    {
        /** @var FeedEntry $entry */
        $entry = $context->getEntity()->getInstance();

        $this->bus->dispatch(new ApproveEntries($entry->getId()));

        $this->addFlash('notice', sprintf('Approved entry: %s', $entry->getTitle()));

        return $this->redirect($this->referrerOrIndex($context->getRequest()));
    }

    /**
     * Declared after the other custom actions on purpose: EasyAdmin registers
     * custom routes in method order and this one is a catch-all.
     */
    #[AdminRoute('/{entityId<.+>}', name: 'detail', options: ['methods' => ['GET']])]
    public function detail(AdminContext $context): KeyValueStore|Response
    {
        return parent::detail($context);
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
