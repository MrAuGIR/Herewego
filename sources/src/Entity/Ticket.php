<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $askedAt;

    #[ORM\Column(type: "integer")]
    private ?int $countPlaces;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $commentary;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $isValidate;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $validateAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $emailSendAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "tickets")]
    #[ORM\JoinColumn(nullable: "true")]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Transport::class, inversedBy: "tickets")]
    private ?Transport $transport;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAskedAt(): ?\DateTimeInterface
    {
        return $this->askedAt;
    }

    public function setAskedAt(\DateTimeInterface $askedAt): self
    {
        $this->askedAt = $askedAt;

        return $this;
    }

    public function getCountPlaces(): ?int
    {
        return $this->countPlaces;
    }

    public function setCountPlaces(int $countPlaces): self
    {
        $this->countPlaces = $countPlaces;

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

    public function getIsValidate(): ?bool
    {
        return $this->isValidate;
    }

    public function setIsValidate(bool $isValidate): self
    {
        $this->isValidate = $isValidate;

        return $this;
    }

    public function getValidateAt(): ?\DateTimeInterface
    {
        return $this->validateAt;
    }

    public function setValidateAt(\DateTimeInterface $validateAt): self
    {
        $this->validateAt = $validateAt;

        return $this;
    }

    public function getEmailSendAt(): ?\DateTimeInterface
    {
        return $this->emailSendAt;
    }

    public function setEmailSendAt(?\DateTimeInterface $emailSendAt): self
    {
        $this->emailSendAt = $emailSendAt;

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

    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function setTransport(?Transport $transport): self
    {
        $this->transport = $transport;

        return $this;
    }
}
