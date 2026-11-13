<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie le durcissement de l'inscription : le honeypot rejette les bots
 * sans créer de compte.
 */
final class RegistrationSecurityTest extends WebTestCase
{
    public function testHoneypotSubmissionCreatesNoUser(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/register/user');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Inscription')->form();
        $form['register[homepage]'] = 'http://bot.example';
        $client->submit($form);

        // Le honeypot rempli court-circuite : on redirige vers le login en silence.
        self::assertResponseRedirects('/login');

        self::assertSame(
            0,
            (int) self::getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->count([]),
            'Une soumission honeypot ne doit créer aucun utilisateur',
        );
    }
}
