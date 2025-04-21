<?php

namespace App\Message;

use App\Entity\User;

class SendEmailMessage
{
  public function __construct(
    private object $object,
    private string $match,
    private ?User  $user
  ) {}

  public function getObject(): Object
  {
    return $this->object;
  }

  public function getMatch(): string
  {
    return $this->match;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setObject(object $object): void
  {
    $this->object = $object;
  }

  public function setMatch(string $match): void
  {
    $this->match = $match;
  }

  public function setUser(?User $user): void
  {
    $this->user = $user;
  }
}
