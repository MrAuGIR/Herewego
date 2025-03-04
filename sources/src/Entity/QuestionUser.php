<?php

namespace App\Entity;

use App\Repository\QuestionUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionUserRepository::class)]
class QuestionUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La question est obligatoire.")]
    #[Assert\Length(min: 20, max: 255, minMessage: "La question doit faire au moins 20 caractères", maxMessage: "La question doit faire moins de 255 caractères")]
    private ?string $question;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le sujet est obligatoire.")]
    #[Assert\Length(min: 5, max: 100, minMessage: "Le sujet doit faire au moins 5 caractères", maxMessage: "Le sujet doit faire moins de 100 caractères")]
    private ?string $subject;


    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: "questionUsers")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

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
