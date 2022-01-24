<?php

namespace Crell\Bundle\Planedo\Controller\Admin;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Message\UpdateFeed;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class FeedCrudController extends AbstractCrudController
{
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
            ->displayAsLink()
            ->linkToCrudAction('updateFeed');

        $actions->add(Crud::PAGE_EDIT, $updateFeed);
        $actions->add(Action::INDEX, $updateFeed);

        $updateFeeds = Action::new('updateFeeds', 'Update')
            ->linkToCrudAction('batchUpdateFeeds')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-check')
        ;

        $actions->addBatchAction($updateFeeds);

        return $actions;
    }

    public function batchUpdateFeeds(BatchActionDto $context, MessageBusInterface $bus): Response
    {
        $ids = $context->getEntityIds();
        foreach ($ids as $id) {
            $bus->dispatch(new UpdateFeed($id));
        }

        $this->addFlash('notice', sprintf('Update queued for %d feeds.', count($ids)));

        return $this->redirect($context->getReferrerUrl());
    }

    public function updateFeed(AdminContext $context, MessageBusInterface $bus): Response
    {
        /** @var Feed $feed */
        $feed = $context->getEntity()->getInstance();

        if (!$feed->isActive()) {
            $this->addFlash('warning', sprintf('Feed %s (%d) is inactive, so will not be updated.', $feed->getTitle(), $feed->getId()));
            return $this->redirect($context->getReferrer());
        }

        $bus->dispatch(new UpdateFeed($feed->getId()));

        $this->addFlash('notice', sprintf('Feed %s (%d) queued for updating', $feed->getTitle(), $feed->getId()));

        return $this->redirect($context->getReferrer());
    }

}
