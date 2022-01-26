<?php

namespace Crell\Bundle\Planedo\Controller\Admin;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\ApproveEntries;
use Crell\Bundle\Planedo\Message\RejectEntries;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class FeedEntryCrudController extends AbstractCrudController
{
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
//            AssociationField::new('feed')->setDisabled()->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $rejectEntry = Action::new('rejectEntry', 'Reject')
            ->displayAsLink()
            ->linkToCrudAction('rejectEntry')
            ->displayIf(static fn (FeedEntry $entry): bool => $entry->isApproved());

        $approveEntry = Action::new('approveEntry', 'Approve')
            ->displayAsLink()
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

    public function batchRejectEntries(BatchActionDto $context, MessageBusInterface $bus): Response
    {
        $ids = $context->getEntityIds();
        $bus->dispatch(new RejectEntries(...$ids));

        $this->addFlash('notice', sprintf('%d entries rejected.', count($ids)));

        return $this->redirect($context->getReferrerUrl());
    }

    public function rejectEntry(AdminContext $context, MessageBusInterface $bus): Response
    {
        /** @var FeedEntry $entry */
        $entry = $context->getEntity()->getInstance();

        $bus->dispatch(new RejectEntries($entry->getId()));

        $this->addFlash('notice', sprintf('Rejected entry: %s', $entry->getTitle()));

        return $this->redirect($context->getReferrer());
    }

    public function batchApproveEntries(BatchActionDto $context, MessageBusInterface $bus): Response
    {
        $ids = $context->getEntityIds();
        foreach ($ids as $id) {
            $bus->dispatch(new ApproveEntries($id));
        }

        $this->addFlash('notice', sprintf('%d entries approved.', count($ids)));

        return $this->redirect($context->getReferrerUrl());
    }

    public function approveEntry(AdminContext $context, MessageBusInterface $bus): Response
    {
        /** @var FeedEntry $entry */
        $entry = $context->getEntity()->getInstance();

        $bus->dispatch(new ApproveEntries($entry->getId()));

        $this->addFlash('notice', sprintf('Approved entry: %s', $entry->getTitle()));

        return $this->redirect($context->getReferrer());
    }

}
