<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Ticket;
use App\Entity\Transport;
use App\Tests\Support\FunctionalTestCase;

final class TransportFlowTest extends FunctionalTestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function transportValues(int $places = 3): array
    {
        $day = (new \DateTime('+1 month'))->format('Y-m-d');
        $values = [
            'transport[goStartedAt]' => $day.'T08:00',
            'transport[goEndedAt]' => $day.'T10:00',
            'transport[returnStartedAt]' => $day.'T20:00',
            'transport[returnEndedAt]' => $day.'T22:00',
            'transport[placePrice]' => '12',
            'transport[totalPlace]' => (string) $places,
            'transport[commentary]' => 'RDV devant la gare',
        ];
        foreach (['localisation_start', 'localisation_return'] as $loc) {
            $values["transport[$loc][cityName]"] = 'Annecy';
            $values["transport[$loc][cityCp]"] = '74000';
            $values["transport[$loc][adress]"] = '1 avenue du lac';
        }

        return $values;
    }

    public function testParticipantCreatesEditsAndDeletesTransport(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $driver = $this->createUser('driver@test.fr');
        $this->createParticipation($event, $driver);
        $this->loginAs($driver);

        $this->client->request('GET', '/transport/event/'.$event->getId());
        self::assertResponseIsSuccessful();

        $this->submitForm('/transport/create/'.$event->getId(), 'transport', self::transportValues());
        self::assertResponseRedirects('/transport/event/'.$event->getId());
        $transport = $this->em()->getRepository(Transport::class)->findOneBy(['user' => $driver]);
        self::assertNotNull($transport);
        self::assertSame(3, $transport->getRemainingPlace());

        // Un seul transport par participant et par évènement.
        $this->client->request('GET', '/transport/create/'.$event->getId());
        self::assertResponseRedirects('/');

        $this->submitForm('/transport/edit/'.$transport->getId(), 'transport', ['transport[commentary]' => 'Nouveau RDV']);
        self::assertResponseRedirects('/transport/event/'.$event->getId());

        $this->client->request('GET', '/transport/manage/'.$transport->getId());
        self::assertResponseIsSuccessful();
        $token = $this->csrfFrom('/transport/manage/'.$transport->getId(), '/transport/delete/');
        $this->client->request('POST', '/transport/delete/'.$transport->getId(), ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $this->client->request('POST', '/transport/delete/'.$transport->getId(), ['_token' => $token]);
        self::assertResponseRedirects('/event/show/'.$event->getId());
    }

    public function testInvalidTransportIsNotSaved(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $driver = $this->createUser();
        $this->createParticipation($event, $driver);
        $this->loginAs($driver);

        $values = self::transportValues();
        $values['transport[goEndedAt]'] = (new \DateTime('-1 year'))->format('Y-m-d\TH:i');
        $this->submitForm('/transport/create/'.$event->getId(), 'transport', $values);

        self::assertResponseIsSuccessful();
        self::assertSame(0, $this->em()->getRepository(Transport::class)->count([]));
    }

    public function testTicketRequestValidationAndCancellation(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);
        $driver = $this->createUser('driver@test.fr');
        $passenger = $this->createUser('passenger@test.fr');
        $this->createParticipation($event, $driver);
        $this->createParticipation($event, $passenger);
        $transport = $this->createTransport($event, $driver, places: 3);

        // Le passager demande 2 places.
        $this->loginAs($passenger);
        $this->submitForm('/transport/show/'.$transport->getId(), 'ticket', ['ticket[countPlaces]' => '2', 'ticket[commentary]' => 'Merci !']);
        self::assertResponseRedirects('/transport/show/'.$transport->getId());
        $ticket = $this->em()->getRepository(Ticket::class)->findOneBy(['user' => $passenger]);
        self::assertNotNull($ticket);
        $ticketId = $ticket->getId();

        // Le passager ne peut pas valider lui-même.
        $this->client->request('POST', '/transport/manage/accept/'.$ticketId, ['_token' => 'x']);
        self::assertResponseRedirects('/');

        // Le conducteur valide puis refuse.
        $this->loginAs($driver);
        $accept = $this->csrfFrom('/transport/manage/'.$transport->getId(), '/transport/manage/accept/');
        $this->client->request('POST', '/transport/manage/accept/'.$ticketId, ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $this->client->request('POST', '/transport/manage/accept/'.$ticketId, ['_token' => $accept]);
        self::assertResponseRedirects('/transport/manage/'.$transport->getId());
        $this->em()->clear();
        self::assertSame(1, $this->em()->find(Transport::class, $transport->getId())?->getRemainingPlace());

        $decline = $this->csrfFrom('/transport/manage/'.$transport->getId(), '/transport/manage/decline/');
        $this->client->request('POST', '/transport/manage/decline/'.$ticketId, ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $this->client->request('POST', '/transport/manage/decline/'.$ticketId, ['_token' => $decline]);
        self::assertResponseRedirects('/transport/manage/'.$transport->getId());
        $this->em()->clear();
        self::assertSame(3, $this->em()->find(Transport::class, $transport->getId())?->getRemainingPlace());

        // Le passager annule sa demande.
        $this->loginAs($passenger);
        $cancel = $this->csrfFrom('/transport/show/'.$transport->getId(), '/cancelTicket/');
        $this->client->request('POST', '/transport/'.$transport->getId().'/cancelTicket/'.$ticketId, ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $this->client->request('POST', '/transport/'.$transport->getId().'/cancelTicket/'.$ticketId, ['_token' => $cancel]);
        self::assertResponseRedirects('/transport/show/'.$transport->getId());
        $this->em()->clear();
        self::assertNull($this->em()->find(Ticket::class, $ticketId));
    }

    public function testNonParticipantCannotSeeTransports(): void
    {
        $event = $this->createEvent($this->createOrganizer());
        $this->loginAs($this->createUser());

        $this->client->request('GET', '/transport/event/'.$event->getId());

        self::assertResponseRedirects('/');
    }
}
