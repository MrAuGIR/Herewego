<?php

namespace App\Entity;

use App\Repository\EventSocialNetworkLinkRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventSocialNetworkLinkRepository::class)
 */
class EventSocialNetworkLink
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
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="EventSocialNetworkLinks")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity=SocialNetwork::class, inversedBy="EventSocialNetworkLinks")
     */
    private $socialNetwork;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

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

    public function getSocialNetwork(): ?SocialNetwork
    {
        return $this->socialNetwork;
    }

    public function setSocialNetwork(?SocialNetwork $socialNetwork): self
    {
        $this->socialNetwork = $socialNetwork;

        return $this;
    }
}
