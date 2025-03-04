<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ["email"], message: "l'email est déjà utilisée")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(type: "string")]
    private string $password;

    #[Assert\EqualTo(propertyPath: "password", message: "mot de passe non identique")]
    public ?string $confirmPassword = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $lastname;

    #[ORM\Column(type: "string", length: 50)]
    private string $firstname;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "boolean", nullable: true, options: ["default" => null])]
    private ?bool $isValidate = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $registerAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $validatedAt = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $pathAvatar = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $webSite = null;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private bool $isPremium = false;

    #[ORM\ManyToOne(targetEntity: Localisation::class, inversedBy: "users", cascade: ["remove"])]
    private ?Localisation $localisation = null;

    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $tickets;

    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $participations;

    #[ORM\OneToMany(targetEntity: QuestionUser::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $questionUsers;

    #[ORM\OneToMany(targetEntity: Transport::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $transports;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $events;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->questionUsers = new ArrayCollection();
        $this->transports = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

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

    public function getIsValidate(): ?bool
    {
        return $this->isValidate;
    }

    public function setIsValidate(bool $isValidate): self
    {
        $this->isValidate = $isValidate;

        return $this;
    }

    public function getRegisterAt(): ?\DateTimeInterface
    {
        return $this->registerAt;
    }

    public function setRegisterAt(\DateTimeInterface $registerAt): self
    {
        $this->registerAt = $registerAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getPathAvatar(): ?string
    {
        return $this->pathAvatar;
    }

    public function setPathAvatar(?string $pathAvatar): self
    {
        $this->pathAvatar = $pathAvatar;

        return $this;
    }

    public function getWebSite(): ?string
    {
        return $this->webSite;
    }

    public function setWebSite(?string $webSite): self
    {
        $this->webSite = $webSite;

        return $this;
    }

    public function getIsPremium(): ?bool
    {
        return $this->isPremium;
    }

    public function setIsPremium(bool $isPremium): self
    {
        $this->isPremium = $isPremium;

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
            $ticket->setUser($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getUser() === $this) {
                $ticket->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Participation[]
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->participations->contains($participation)) {
            $this->participations[] = $participation;
            $participation->setUser($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getUser() === $this) {
                $participation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|QuestionUser[]
     */
    public function getQuestionUsers(): Collection
    {
        return $this->questionUsers;
    }

    public function addQuestionUser(QuestionUser $questionUser): self
    {
        if (!$this->questionUsers->contains($questionUser)) {
            $this->questionUsers[] = $questionUser;
            $questionUser->setUser($this);
        }

        return $this;
    }

    public function removeQuestionUser(QuestionUser $questionUser): self
    {
        if ($this->questionUsers->removeElement($questionUser)) {
            // set the owning side to null (unless already changed)
            if ($questionUser->getUser() === $this) {
                $questionUser->setUser(null);
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
            $transport->setUser($this);
        }

        return $this;
    }

    public function removeTransport(Transport $transport): self
    {
        if ($this->transports->removeElement($transport)) {
            // set the owning side to null (unless already changed)
            if ($transport->getUser() === $this) {
                $transport->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return implode(',',$this->getRoles());
        
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}
