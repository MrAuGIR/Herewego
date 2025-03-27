<?php

namespace App\Service\Mail\Rules;

use App\Entity\Transport;
use App\Entity\User;
use App\Service\Mail\Rules\MailerRuleInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EventTransportSender implements MailerRuleInterface
{
    public const MATCH = 'event_transport';

    public function __construct(
        protected MailerInterface $mailer,
        protected LoggerInterface $mailerLogger
    ) {
    }

    public function support(string $match, mixed $subject): bool
    {
        return $match === self::MATCH && $subject instanceof Transport;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(object $subject, ?User $user): void
    {
        /** @var Transport $transport */
        $transport = $subject;
        try {
            $email = new TemplatedEmail();
            $email->from(new Address('admin@gmail.com', 'Admin'))
                ->subject("Participation au Transport de l'event : ".$transport->getEvent()->getTitle())
                ->to($user->getEmail())
                ->htmlTemplate('emails/transport_event.html.twig')
                ->context([
                    'user' => $user,
                    'event' => $transport->getEvent(),
                    'transport' => $transport,
                ]);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->mailerLogger->error('Error while sending Email ['.self::MATCH.'], '.$e->getMessage());
        }
    }
}