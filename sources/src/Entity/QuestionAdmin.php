<?php

namespace App\Entity;

use App\Repository\QuestionAdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: QuestionAdminRepository::class)]
class QuestionAdmin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La question est obligatoire.")]
    private string $question;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La réponse est obligatoire.")]
    private string $answer;

    #[ORM\Column(type: "integer")]
    #[Assert\Positive(message: "Le nombre doit être positif")]
    private int $importance;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;
        return $this;
    }

    public function getImportance(): ?int
    {
        return $this->importance;
    }

    public function setImportance(int $importance): self
    {
        $this->importance = $importance;
        return $this;
    }
}
