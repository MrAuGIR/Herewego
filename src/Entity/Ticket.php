<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TicketRepository::class)
 */
class Ticket
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
    private $askedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $countPlaces;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentary;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validateAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailSendAt;

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
}
