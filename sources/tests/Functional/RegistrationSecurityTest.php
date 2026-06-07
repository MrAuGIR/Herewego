<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
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

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        $crawler = $client->request('GET', '/register/user');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Inscription')->form();
        $form['register[homepage]'] = 'http://bot.example';
        $client->submit($form);

        // Le honeypot rempli court-circuite : on redirige vers le login en silence.
        self::assertResponseRedirects('/login');

        self::assertSame(
            0,
            (int) $em->getRepository(User::class)->count([]),
            'Une soumission honeypot ne doit créer aucun utilisateur',
        );
    }
}
