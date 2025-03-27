<?php

namespace App\Service\Mail;

use App\Entity\User;
use App\Service\Mail\Rules\MailerRuleInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class Sender
{
    public const EVENT_TRANSPORT = 'event_transport';

    public const EVENT_PARTICIPATION = 'event_participation';

    public const EVENT_DELETE = 'event_delete';

    public function __construct(
        #[AutowireIterator('app.mailer.sender')] private readonly iterable $senders,
    ) {
    }

    public function send(object $object, string $match, ?User $user): void
    {
        /** @var MailerRuleInterface $sender */
        foreach ($this->senders as $sender) {
            if ($sender->support($match, $object)) {
                $sender->send($object, $user);
            }
        }
    }

}
