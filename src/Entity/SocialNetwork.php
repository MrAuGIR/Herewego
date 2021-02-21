<?php

namespace App\Entity;

use App\Repository\SocialNetworkRepository;
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
}
