<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\QuestionUser;
use App\Entity\User;
use App\Tests\Support\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RegistrationAndFaqTest extends FunctionalTestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function registrations(): iterable
    {
        yield 'utilisateur' => ['/register/user', 'ROLE_USER'];
        yield 'organisateur' => ['/register/organizer', 'ROLE_ORGANIZER'];
    }

    #[DataProvider('registrations')]
    public function testRegistrationCreatesPendingAccount(string $uri, string $role): void
    {
        $values = [
            'register[email]' => 'new@test.fr',
            'register[password]' => 'Secret123',
            'register[confirmPassword]' => 'Secret123',
            'register[lastname]' => 'Martin',
            'register[firstname]' => 'Léa',
            'register[phone]' => '0601020304',
            'register[localisation][cityName]' => 'Lyon',
            'register[localisation][cityCp]' => '69000',
            'register[localisation][adress]' => '3 rue Neuve',
        ];
        if ('ROLE_ORGANIZER' === $role) {
            $values['register[companyName]'] = 'Asso';
            $values['register[siret]'] = '49098556100011';
        }

        $this->submitForm($uri, 'register', $values);

        self::assertResponseRedirects('/login');
        $user = $this->em()->getRepository(User::class)->findOneBy(['email' => 'new@test.fr']);
        self::assertNotNull($user);
        self::assertFalse($user->getIsValidate(), 'validation admin requise');
        self::assertContains($role, $user->getRoles());
        self::assertNotSame('Secret123', $user->getPassword());
    }

    public function testRegistrationWithMismatchingPasswordsIsRejected(): void
    {
        $this->submitForm('/register/user', 'register', [
            'register[email]' => 'new@test.fr',
            'register[password]' => 'Secret123',
            'register[confirmPassword]' => 'Other123',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(0, $this->em()->getRepository(User::class)->count([]));
    }

    public function testOrganizerHoneypotIsSilentlyIgnored(): void
    {
        $this->submitForm('/register/organizer', 'register', ['register[homepage]' => 'http://bot.example']);

        self::assertResponseRedirects('/login');
        self::assertSame(0, $this->em()->getRepository(User::class)->count([]));
    }

    public function testQuestionIsStoredWithAuthor(): void
    {
        $user = $this->createUser();
        $this->loginAs($user);

        // Le lien « Posez-la nous ! » de la FAQ ouvre le formulaire en GET.
        $this->submitForm('/faq/question', 'faq', ['faq[subject]' => 'x']);
        self::assertResponseIsSuccessful(); // invalide : formulaire ré-affiché

        $this->submitForm('/faq/question', 'faq', [
            'faq[subject]' => 'Paiement',
            'faq[question]' => 'Comment se passe le paiement du covoiturage ?',
        ]);

        self::assertResponseRedirects('/faq/');
        $question = $this->em()->getRepository(QuestionUser::class)->findOneBy(['subject' => 'Paiement']);
        self::assertNotNull($question);
        self::assertSame($user->getId(), $question->getUser()?->getId());
    }
}
