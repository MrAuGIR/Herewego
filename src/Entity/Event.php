<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countViews;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tag;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity=EventGroup::class, inversedBy="Events")
     */
    private $eventGroup;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="events")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity=Picture::class, mappedBy="event")
     */
    private $pictures;

    /**
     * @ORM\OneToMany(targetEntity=EventSocialNetworkLink::class, mappedBy="event")
     */
    private $EventSocialNetworkLinks;

    /**
     * @ORM\OneToMany(targetEntity=Transport::class, mappedBy="event")
     */
    private $transports;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->EventSocialNetworkLinks = new ArrayCollection();
        $this->transports = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCountViews(): ?int
    {
        return $this->countViews;
    }

    public function setCountViews(?int $countViews): self
    {
        $this->countViews = $countViews;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEventGroup(): ?EventGroup
    {
        return $this->eventGroup;
    }

    public function setEventGroup(?EventGroup $eventGroup): self
    {
        $this->eventGroup = $eventGroup;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setEvent($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getEvent() === $this) {
                $picture->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EventSocialNetworkLink[]
     */
    public function getEventSocialNetworkLinks(): Collection
    {
        return $this->EventSocialNetworkLinks;
    }

    public function addEventSocialNetworkLink(EventSocialNetworkLink $eventSocialNetworkLink): self
    {
        if (!$this->EventSocialNetworkLinks->contains($eventSocialNetworkLink)) {
            $this->EventSocialNetworkLinks[] = $eventSocialNetworkLink;
            $eventSocialNetworkLink->setEvent($this);
        }

        return $this;
    }

    public function removeEventSocialNetworkLink(EventSocialNetworkLink $eventSocialNetworkLink): self
    {
        if ($this->EventSocialNetworkLinks->removeElement($eventSocialNetworkLink)) {
            // set the owning side to null (unless already changed)
            if ($eventSocialNetworkLink->getEvent() === $this) {
                $eventSocialNetworkLink->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transport[]
     */
    public function getTransports(): Collection
    {
        return $this->transports;
    }

    public function addTransport(Transport $transport): self
    {
        if (!$this->transports->contains($transport)) {
            $this->transports[] = $transport;
            $transport->setEvent($this);
        }

        return $this;
    }

    public function removeTransport(Transport $transport): self
    {
        if ($this->transports->removeElement($transport)) {
            // set the owning side to null (unless already changed)
            if ($transport->getEvent() === $this) {
                $transport->setEvent(null);
            }
        }

        return $this;
    }
}
