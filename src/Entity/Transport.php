<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\ORM\Mapping as ORM;

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
     */
    private $goStartedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $goEndedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $returnStartedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $returnEndedAt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $placePrice;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalPlace;

    /**
     * @ORM\Column(type="integer")
     */
    private $remainingPlace;

    /**
     * @ORM\Column(type="text", nullable=true)
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
}
