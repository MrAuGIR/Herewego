<?php

namespace App\Service\Mail\Rules;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'app.mailer.sender')]
interface MailerRuleInterface
{
  public function support(string $match, mixed $subject): bool;

  public function send(object $subject, ?User $user): void;

}
