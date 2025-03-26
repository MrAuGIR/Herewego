<?php

namespace App\Service\Mail\Rules;

use App\Entity\Event;
use App\Entity\User;
use App\Service\Mail\Rules\MailerRuleInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EventParticipationSender implements MailerRuleInterface
{
    public function __construct(
        protected MailerInterface $mailer,
        protected LoggerInterface $mailerLogger
    ) {
    }

    public const MATCH = 'event_participation';
    public function support(string $match, mixed $subject): bool
    {
        return $match === self::MATCH && $subject instanceof Event;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(object $subject, ?User $user): void
    {
        /** @var Event $event */
        $event = $subject;
        try {
            $email = new TemplatedEmail();
            $email->from(new Address('admin@gmail.com', 'Admin'))
                ->subject("Participation à l'évênement : ".$event->getTitle())
                ->to($user->getEmail())
                ->htmlTemplate('emails/participation_event.html.twig')
                ->context([
                    'user' => $user,
                    'event' => $event,
                ]);
            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->mailerLogger->error('Error while sending Email ['.self::MATCH.'], '.$e->getMessage());
        }
    }
}