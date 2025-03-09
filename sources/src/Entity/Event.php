<?php

namespace App\Entity;

use App\Repository\EventRepository;
use App\Tools\TagService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'La question doit faire au moins 5 caractères', maxMessage: 'Le titre doit faire moins de 255 caractères')]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title;

    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[ORM\Column(type: 'text')]
    private ?string $description;

    #[Assert\NotBlank(message: "La date de début d'événement est obligatoire.")]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $startedAt;

    #[Assert\NotBlank(message: "La date de fin d'événement est obligatoire.")]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $endedAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $website;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $countViews;

    #[ORM\Column(type: 'text')]
    private ?string $tag;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug;

    #[ORM\ManyToOne(targetEntity: EventGroup::class, inversedBy: 'Events')]
    private ?EventGroup $eventGroup;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'events')]
    #[Assert\NotBlank(message: 'La catégorie est obligatoire.')]
    private ?Category $category;

    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'event', cascade: ['persist', 'remove'])]
    private Collection $pictures;

    #[ORM\OneToMany(targetEntity: Transport::class, mappedBy: 'event', cascade: ['remove'])]
    private Collection $transports;

    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'event', cascade: ['remove'])]
    private Collection $participations;

    #[ORM\ManyToOne(targetEntity: Localisation::class, cascade: ['persist','remove'], inversedBy: 'events')]
    private ?Localisation $localisation;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $facebookLink;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $instagramLink;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $twitterLink;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    private ?User $user;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->transports = new ArrayCollection();
        $this->participations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): self
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

    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (! $this->pictures->contains($picture)) {
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

    public function getTransports(): Collection
    {
        return $this->transports;
    }

    public function addTransport(Transport $transport): self
    {
        if (! $this->transports->contains($transport)) {
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

    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (! $this->participations->contains($participation)) {
            $this->participations[] = $participation;
            $participation->setEvent($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getEvent() === $this) {
                $participation->setEvent(null);
            }
        }

        return $this;
    }

    public function getLocalisation(): ?Localisation
    {
        return $this->localisation;
    }

    public function setLocalisation(?Localisation $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): self
    {
        $this->facebookLink = $facebookLink;

        return $this;
    }

    public function getInstagramLink(): ?string
    {
        return $this->instagramLink;
    }

    public function setInstagramLink(?string $instagramLink): self
    {
        $this->instagramLink = $instagramLink;

        return $this;
    }

    public function getTwitterLink(): ?string
    {
        return $this->twitterLink;
    }

    public function setTwitterLink(?string $twitterLink): self
    {
        $this->twitterLink = $twitterLink;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * getTagCode
     *Retour le code de l'event pour son tag html.
     */
    public function getTagCode(): string
    {
        $tagCode = '';
        $tagService = new TagService();
        $tagCode = $tagService->code().'-'.$tagService->year($this->getStartedAt()).$tagService->department($this->getLocalisation()->getCityCp());

        return $tagCode;
    }
}
