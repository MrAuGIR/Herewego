<?php
namespace App\Command;

use App\Entity\Transport;
use App\Entity\User;
use App\Repository\TransportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendEmailCommand extends Command
{

    public static $defaultName = "app:send-email-transport";
    private $transportRepository;
    private $userRepository;
    private $mailer;


    public function __construct(TransportRepository $transportRepository, UserRepository $userRepository, MailerInterface $mailer)
    {
        $this->transportRepository = $transportRepository;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Envoie d\'email rappel aux participants de transport avant leur départ')
            ->setHelp('Cette commande vous permet d\'envoier des emails aux participants de transport avant leur départ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numberEmail = 0;
        /* code pour envoyer des emails deux jours avant le debut du transport*/
        
        //on recupère les transports concernés 
        $transports = $this->transportRepository->findTransportToAlert();

        // pour chaque transports on recupère les tickets et donc le user auquel il appartient
        foreach($transports as $transport){

            $tickets = $transport->getTickets();

            foreach($tickets as $ticket){

                if($ticket->getIsValidate() == true){
                    $numberEmail++;
                    $user = $ticket->getUser();
                    $this->SendEmail($transport, $user);
                }   
                
            }
        }
        $output->writeln($numberEmail.' email envoyé');
        $output->writeln('Email successfully generated!');
            
        return Command::SUCCESS;
    }

    public function SendEmail(Transport $transport, User $user){

        $email = new TemplatedEmail();
        $email->from(new Address("admin@gmail.com", "Admin"))
               ->subject("Participation au Transport de l'event : " . $transport->getEvent()->getTitle())
            ->to($user->getEmail())
            ->htmlTemplate("emails/transport_event.html.twig")
            ->context([
                'user' => $user,
                'event' => $transport->getEvent(),
                'transport' => $transport
            ]);

        $this->mailer->send($email);
    }

}