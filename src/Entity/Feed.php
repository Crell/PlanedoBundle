<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Entity;

use Crell\Bundle\Planedo\Repository\FeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedRepository::class)]
class Feed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /**
     * The URL of the machine-parsable RSS/Atom feed.
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $feedLink;

    /**
     * The last time this feed was updated from its source.
     *
     * This is not a field in the feed itself.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastUpdated;

    #[ORM\Column(type: 'boolean')]
    private $active = false;

    #[ORM\OneToMany(
        mappedBy: 'feed',
        targetEntity: FeedEntry::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $entries;

    // The fields below here are data from the feed, cached locally.

    /**
     * The HTML link of this feed, NOT the XML feed.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $link = '';

    #[ORM\Column(type: 'array')]
    private array $authors = [];

    #[ORM\Column(type: 'string', length: 255)]
    private string $copyright = '';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateCreated;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateModified;

    #[ORM\Column(type: 'string', length: 255)]
    private string $generator = '';

    #[ORM\Column(type: 'string', length: 6)]
    private string $language = '';

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|FeedEntry[]
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(FeedEntry $entry): self
    {
        // The default Collection::contains() logic seems
        // like it doesn't work, so do it manually.
        /** @var FeedEntry $existing */
        foreach ($this->entries as $key => $existing) {
            if ($existing->getId() === $entry->getId()) {
                $this->entries->set($key, $entry);

                return $this;
            }
        }

        $this->entries[] = $entry;
        $entry->setFeed($this);

        return $this;
    }

    public function removeEntry(FeedEntry $entry): self
    {
        if ($this->entries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getFeed() === $this) {
                $entry->setFeed(null);
            }
        }

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

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

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): self
    {
        $this->copyright = $copyright;

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

    public function getGenerator(): ?string
    {
        return $this->generator;
    }

    public function setGenerator(?string $generator): self
    {
        $this->generator = $generator;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getFeedLink(): ?string
    {
        return $this->feedLink;
    }

    public function setFeedLink(?string $feedLink): self
    {
        $this->feedLink = $feedLink;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
