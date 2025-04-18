<?php

namespace App\Command;

use App\Entity\Transport;
use App\Entity\User;
use App\Repository\TransportRepository;
use App\Service\Mail\Sender;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:send-email-transport')]
class SendEmailCommand extends Command
{
    public function __construct(
        private readonly TransportRepository $transportRepository,
        private readonly Sender $sender,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Envoie d\'email rappel aux participants de transport avant leur départ')
            ->setHelp('Cette commande vous permet d\'envoier des emails aux participants de transport avant leur départ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $numberEmail = 0;

        $transports = $this->transportRepository->findTransportToAlert();

        foreach ($transports as $transport) {
            $tickets = $transport->getTickets();

            foreach ($tickets as $ticket) {
                if ($ticket->getIsValidate()) {
                    ++$numberEmail;
                    $user = $ticket->getUser();
                    $this->sendEmail($transport, $user);
                }
            }
        }
        $output->writeln($numberEmail.' email envoyé');
        $output->writeln('Email successfully generated!');

        return Command::SUCCESS;
    }

    public function sendEmail(Transport $transport, User $user): void
    {
        $this->sender->send($transport, Sender::EVENT_TRANSPORT ,$user);
    }
}
