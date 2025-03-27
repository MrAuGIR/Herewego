<?php 

namespace App\Service\Mail\Rules;

use App\Entity\Event;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class DeleteTransportSender implements MailerRuleInterface
{
    public function __construct(
        protected MailerInterface $mailer,
        protected LoggerInterface $mailerLogger
    ) {
    }

    public const DELETE_TRANSPORT = 'DELETE_TRANSPORT';

    public function support(string $match, mixed $subject): bool
    {
        return $match === self::DELETE_TRANSPORT && $subject instanceof Event;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(object $subject, ?User $user): void
    {
        /** @var Event $event */
        $event = $subject;

        $transportManagerMails = [];
        $ticketUserMails = [];

        foreach ($event->getTransports() as $transport) {
            $transportManagerMails[] = $transport->getUser()->getEmail();

            $tickets = $transport->getTickets();
            foreach ($tickets as $ticket) {
                $ticketUserMails[] = $ticket->getUser()->getEmail();
            }
        }

        try {
            $email = new TemplatedEmail();
            $email->from(new Address('admin@gmail.com', 'Admin'))
                ->subject("Annulation de l'évênement : ".$event->getTitle())
                ->to(...$transportManagerMails, ...$ticketUserMails)
                ->htmlTemplate('emails/annulation_event.html.twig')
                ->context([
                    'event' => $event,
                ]);
            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->mailerLogger->error('Error while sending Email, ['.self::DELETE_TRANSPORT.']'.$e->getMessage());
        }
    }
}

