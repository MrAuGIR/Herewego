<?php

namespace App\Entity;

use App\Repository\EventGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: EventGroupRepository::class)]
class EventGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pathImage;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'eventGroup')]
    private ArrayCollection $Events;

    public function __construct()
    {
        $this->Events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPathImage(): ?string
    {
        return $this->pathImage;
    }

    public function setPathImage(?string $pathImage): self
    {
        $this->pathImage = $pathImage;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->Events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->Events->contains($event)) {
            $this->Events[] = $event;
            $event->setEventGroup($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->Events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getEventGroup() === $this) {
                $event->setEventGroup(null);
            }
        }

        return $this;
    }
}
