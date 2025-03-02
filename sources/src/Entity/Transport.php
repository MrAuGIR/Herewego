<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TransportRepository::class)
 */
class Transport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message =" Veuillez saisir la date de départ allé ")
     */
    private $goStartedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(message="la date d'arriver allé doit etre après la date de départ allé", propertyPath="goStartedAt")
     * @Assert\NotNull(message =" Veuillez saisir la date d'arrivé' allé ")
     */
    private $goEndedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(message="la date de départ retour doit être après la date d'arrivé allé", propertyPath="goEndedAt")
     * @Assert\NotNull(message =" Veuillez saisir la date départ au retour ")
     */
    private $returnStartedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(message="la date de d'arrivé retour doit être après la date de depart retour", propertyPath="returnStartedAt")
     * @Assert\NotNull(message =" Veuillez saisir la date d'arrivé au retour ")
     */
    private $returnEndedAt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * Assert\Positive(message="le prix doit être positif")
     */
    private $placePrice;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive(message="nombre doit être positif")
     * @Assert\GreaterThan( value = 0)
     * @Assert\NotNull(message="veuillez saisir un nombre de place")
     */
    private $totalPlace;

    /**
     * @ORM\Column(type="integer")
     */
    private $remainingPlace;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank(message="Le champs commentaire est obligatoire")
     */
    private $commentary;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="transports")
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="transport", cascade={"remove"})
     */
    private $tickets;

    /**
     * @ORM\ManyToOne(targetEntity=Localisation::class, inversedBy="transports")
     */
    private $localisation_start;

    /**
     * @ORM\ManyToOne(targetEntity=Localisation::class, inversedBy="transports")
     */
    private $localisation_return;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="transports")
     */
    private $user;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoStartedAt(): ?\DateTimeInterface
    {
        return $this->goStartedAt;
    }

    public function setGoStartedAt(\DateTimeInterface $goStartedAt): self
    {
        $this->goStartedAt = $goStartedAt;

        return $this;
    }

    public function getGoEndedAt(): ?\DateTimeInterface
    {
        return $this->goEndedAt;
    }

    public function setGoEndedAt(\DateTimeInterface $goEndedAt): self
    {
        $this->goEndedAt = $goEndedAt;

        return $this;
    }

    public function getReturnStartedAt(): ?\DateTimeInterface
    {
        return $this->returnStartedAt;
    }

    public function setReturnStartedAt(\DateTimeInterface $returnStartedAt): self
    {
        $this->returnStartedAt = $returnStartedAt;

        return $this;
    }

    public function getReturnEndedAt(): ?\DateTimeInterface
    {
        return $this->returnEndedAt;
    }

    public function setReturnEndedAt(\DateTimeInterface $returnEndedAt): self
    {
        $this->returnEndedAt = $returnEndedAt;

        return $this;
    }

    public function getPlacePrice(): ?string
    {
        return $this->placePrice;
    }

    public function setPlacePrice(string $placePrice): self
    {
        $this->placePrice = $placePrice;

        return $this;
    }

    public function getTotalPlace(): ?int
    {
        return $this->totalPlace;
    }

    public function setTotalPlace(int $totalPlace): self
    {
        $this->totalPlace = $totalPlace;

        return $this;
    }

    public function getRemainingPlace(): ?int
    {
        return $this->remainingPlace;
    }

    public function setRemainingPlace(int $remainingPlace): self
    {
        // securité sur le nombre de place 
        $remainingPlace = ($remainingPlace > $this->getTotalPlace())? $this->getTotalPlace() : $remainingPlace;
        $remainingPlace = ($remainingPlace<0)? 0 : $remainingPlace;
        $this->remainingPlace = $remainingPlace;

        return $this;
    }

    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    public function setCommentary(?string $commentary): self
    {
        $this->commentary = $commentary;

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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setTransport($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getTransport() === $this) {
                $ticket->setTransport(null);
            }
        }

        return $this;
    }

    public function getLocalisationStart(): ?Localisation
    {
        return $this->localisation_start;
    }

    public function setLocalisationStart(?Localisation $localisation_start): self
    {
        $this->localisation_start = $localisation_start;

        return $this;
    }

    public function getLocalisationReturn(): ?Localisation
    {
        return $this->localisation_return;
    }

    public function setLocalisationReturn(?Localisation $localisation_return): self
    {
        $this->localisation_return = $localisation_return;

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
}
