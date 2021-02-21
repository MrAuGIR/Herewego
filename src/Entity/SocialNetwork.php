<?php

namespace App\Entity;

use App\Repository\SocialNetworkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocialNetworkRepository::class)
 */
class SocialNetwork
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pathLogo;

    /**
     * @ORM\OneToMany(targetEntity=EventSocialNetworkLink::class, mappedBy="socialNetwork")
     */
    private $EventSocialNetworkLinks;

    public function __construct()
    {
        $this->EventSocialNetworkLinks = new ArrayCollection();
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

    public function getPathLogo(): ?string
    {
        return $this->pathLogo;
    }

    public function setPathLogo(string $pathLogo): self
    {
        $this->pathLogo = $pathLogo;

        return $this;
    }

    /**
     * @return Collection|EventSocialNetworkLink[]
     */
    public function getEventSocialNetworkLinks(): Collection
    {
        return $this->EventSocialNetworkLinks;
    }

    public function addEventSocialNetworkLink(EventSocialNetworkLink $eventSocialNetworkLink): self
    {
        if (!$this->EventSocialNetworkLinks->contains($eventSocialNetworkLink)) {
            $this->EventSocialNetworkLinks[] = $eventSocialNetworkLink;
            $eventSocialNetworkLink->setSocialNetwork($this);
        }

        return $this;
    }

    public function removeEventSocialNetworkLink(EventSocialNetworkLink $eventSocialNetworkLink): self
    {
        if ($this->EventSocialNetworkLinks->removeElement($eventSocialNetworkLink)) {
            // set the owning side to null (unless already changed)
            if ($eventSocialNetworkLink->getSocialNetwork() === $this) {
                $eventSocialNetworkLink->setSocialNetwork(null);
            }
        }

        return $this;
    }
}
