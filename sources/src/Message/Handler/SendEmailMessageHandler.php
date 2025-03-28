<?php

namespace App\Message\Handler;

use App\Message\SendEmailMessage;
use App\Service\Mail\Sender;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private Sender $sender
    ) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $this->sender->send(
            $message->getObject(),
            $message->getMatch(),
            $message->getUser()
        );
    }
}
