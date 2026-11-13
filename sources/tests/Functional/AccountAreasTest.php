<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Espaces « utilisateur » (/user) et « organisateur » (/organizer).
 */
final class AccountAreasTest extends FunctionalTestCase
{
    private function reload(User $user): User
    {
        $this->em()->clear();
        $fresh = $this->em()->find(User::class, $user->getId());
        self::assertNotNull($fresh);

        return $fresh;
    }

    public function testUserPagesAndProfileEdition(): void
    {
        $organizer = $this->createOrganizer();
        $user = $this->createUser();
        $this->createParticipation($this->createEvent($organizer), $user);
        $this->createParticipation($this->createEvent($organizer, start: '-2 weeks', end: '-1 week'), $user);
        $this->loginAs($user);

        foreach (['/user/profile', '/user/events', '/user/history', '/user/profile/edit', '/user/profile/password'] as $uri) {
            $this->client->request('GET', $uri);
            self::assertResponseIsSuccessful($uri);
        }

        $this->submitForm('/user/profile/edit', 'edit_profil', ['edit_profil[firstname]' => 'Bob']);
        self::assertResponseRedirects('/user/profile');
        self::assertSame('Bob', $this->reload($user)->getFirstname());

        $this->client->request('GET', '/user/profile/avatar/1');
        self::assertResponseIsSuccessful();
        self::assertSame('1', $this->reload($user)->getPathAvatar());
        $this->client->request('GET', '/user/profile/avatar/999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testUserPasswordChange(): void
    {
        $user = $this->createUser();
        $this->loginAs($user);

        $this->submitForm('/user/profile/password', 'edit_pass', ['edit_pass[newPassword]' => 'abc12345', 'edit_pass[newPasswordRepeat]' => 'different']);
        self::assertResponseRedirects('/user/profile/password');

        $this->submitForm('/user/profile/password', 'edit_pass', ['edit_pass[newPassword]' => 'abc12345', 'edit_pass[newPasswordRepeat]' => 'abc12345']);
        self::assertResponseRedirects('/user/profile');
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($this->reload($user), 'abc12345'));
    }

    public function testUserDeletesAccount(): void
    {
        $user = $this->createUser();
        $id = $user->getId();
        $this->loginAs($user);

        $this->client->request('POST', '/user/profile/delete', ['_token' => 'bad']);
        self::assertResponseRedirects('/');

        $token = $this->csrfFrom('/user/profile', '/user/profile/delete');
        $this->client->request('POST', '/user/profile/delete', ['_token' => $token]);
        self::assertResponseRedirects('/');
        $this->em()->clear();
        self::assertNull($this->em()->find(User::class, $id));
    }

    public function testOrganizerPagesAndCsvExports(): void
    {
        $organizer = $this->createOrganizer();
        $this->createEvent($organizer);
        $this->createEvent($organizer, start: '-2 weeks', end: '-1 week');
        $this->loginAs($organizer);

        foreach (['/organizer/profile', '/organizer/events', '/organizer/history', '/organizer/stats', '/organizer/profile/edit'] as $uri) {
            $this->client->request('GET', $uri);
            self::assertResponseIsSuccessful($uri);
        }

        $this->submitForm('/organizer/profile/edit', 'edit_profil', ['edit_profil[companyName]' => 'Nouvelle SARL']);
        self::assertResponseRedirects('/organizer/profile');
        self::assertSame('Nouvelle SARL', $this->reload($organizer)->getCompanyName());

        $this->client->request('GET', '/organizer/profile/avatar/2');
        self::assertResponseIsSuccessful();
        $this->client->request('GET', '/organizer/profile/avatar/999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testOrganizerPasswordAndAccountDeletion(): void
    {
        $organizer = $this->createOrganizer();
        $id = $organizer->getId();
        $this->loginAs($organizer);

        // Sans jeton CSRF : formulaire ré-affiché, rien n'est modifié.
        $crawler = $this->client->request('POST', '/organizer/profile/password', ['edit_pass' => ['newPassword' => 'a', 'newPasswordRepeat' => 'a']]);
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="edit_pass"]')->form();

        $this->client->submit($form, ['edit_pass[newPassword]' => 'a', 'edit_pass[newPasswordRepeat]' => 'b']);
        self::assertResponseRedirects('/organizer/profile/password');

        $this->client->submit($form, ['edit_pass[newPassword]' => 'abc12345', 'edit_pass[newPasswordRepeat]' => 'abc12345']);
        self::assertResponseRedirects('/organizer/profile');

        $this->client->request('POST', '/organizer/profile/delete', ['_token' => 'bad']);
        self::assertResponseRedirects('/');
        $token = $this->csrfFrom('/organizer/profile', '/organizer/profile/delete');
        $this->client->request('POST', '/organizer/profile/delete', ['_token' => $token]);
        self::assertResponseRedirects('/');
        $this->em()->clear();
        self::assertNull($this->em()->find(User::class, $id));
    }

    public function testAreasAreRestricted(): void
    {
        $this->client->request('GET', '/organizer/profile');
        self::assertResponseRedirects('/login');

        $this->loginAs($this->createUser());
        $this->client->request('GET', '/organizer/profile');
        self::assertResponseRedirects('/');
    }

    public function testLoginOfNonValidatedAccountIsRefused(): void
    {
        $this->createUser('pending@test.fr', validated: false);

        $crawler = $this->client->request('GET', '/login');
        $this->client->submit($crawler->filter('form')->form(['_username' => 'pending@test.fr', '_password' => 'password']));
        $this->client->followRedirect();

        self::assertSelectorTextContains('body', 'en attente de validation');
    }
}
