<?php

namespace App\Service\Mail;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Sender
{
    public function __construct(
        protected MailerInterface $mailer,
    ){}

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEventParticipation(Event $event, User $user): void
    {
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
/** @todo  */
        }


    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendDeleteTransports(Event $event, User $user): void
    {
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
        }catch (\Exception $e){
            /** @todo  */
        }
    }
}