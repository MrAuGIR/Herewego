<?php

namespace App\Entity;

use App\Repository\LocalisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LocalisationRepository::class)
 */
class Localisation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message =" Veuillez saisir l'adresse de départ")
     */
    private $adress;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="localisations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="localisation")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="localisation")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=Transport::class, mappedBy="localisation_start")
     */
    private $transports;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message ="le nom de la ville ne peut pas être vide")
     */
    private $cityName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotBlank(message ="le code postal de la ville ne peut pas être vide")
     */
    private $cityCp;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $coordonneesX;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $coordonneesY;

   
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->transports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }


    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setLocalisation($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getLocalisation() === $this) {
                $user->setLocalisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setLocalisation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLocalisation() === $this) {
                $event->setLocalisation(null);
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
            $transport->setLocalisationStart($this);
        }

        return $this;
    }

    public function removeTransport(Transport $transport): self
    {
        if ($this->transports->removeElement($transport)) {
            // set the owning side to null (unless already changed)
            if ($transport->getLocalisationStart() === $this) {
                $transport->setLocalisationStart(null);
            }
        }

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): self
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getCityCp(): ?string
    {
        return $this->cityCp;
    }

    public function setCityCp(?string $cityCp): self
    {
        $this->cityCp = $cityCp;

        return $this;
    }

    public function getCoordonneesX(): ?string
    {
        return $this->coordonneesX;
    }

    public function setCoordonneesX(?string $coordonneesX): self
    {
        $this->coordonneesX = $coordonneesX;

        return $this;
    }

    public function getCoordonneesY(): ?string
    {
        return $this->coordonneesY;
    }

    public function setCoordonneesY(?string $coordonneesY): self
    {
        $this->coordonneesY = $coordonneesY;

        return $this;
    }

}
