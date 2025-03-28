<?php

namespace App\Message\Handler;

use App\Message\SendEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
  public function __invoke(SendEmailMessage $message): void {}
}
