<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Entity;

use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedEntryRepository::class)]
class FeedEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $link;

    #[ORM\ManyToOne(targetEntity: Feed::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    private Feed $feed;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'boolean')]
    private bool $approved = true;

    #[ORM\Column(type: 'array')]
    private array $authors = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateCreated;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateModified;

    public function getId(): string
    {
        return $this->getLink();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getDescription(): ?string
    {
        // If there is already a title link to the article, strip it out.
        // Linking to the article and showing its title is the responsibility
        // of the template, not the body.
        $link = preg_quote($this->getLink(), '%');
        $title = preg_quote($this->getTitle(), '%');
        $regex = "%<a[^<>]*href=\"?{$link}\"?.*{$title}.*</a>%isU";
        $description = preg_replace($regex, '', $this->description);

        // Remove unnessesary whitespace from lines
        $description = implode("\n", array_map(function ($line) { return trim($line); }, explode("\n", $description)));

        return $description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFeed(): ?Feed
    {
        return $this->feed;
    }

    public function setFeed(?Feed $feed): self
    {
        $this->feed = $feed;

        return $this;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getAuthors(): ?array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        if ($dateCreated instanceof \DateTime) {
            $dateCreated = \DateTimeImmutable::createFromMutable($dateCreated);
        }
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeInterface $dateModified): self
    {
        if ($dateModified instanceof \DateTime) {
            $dateModified = \DateTimeImmutable::createFromMutable($dateModified);
        }
        $this->dateModified = $dateModified;

        return $this;
    }
}
