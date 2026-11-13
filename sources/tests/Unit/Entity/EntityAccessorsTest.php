<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\EventGroup;
use App\Entity\Localisation;
use App\Entity\Participation;
use App\Entity\Picture;
use App\Entity\QuestionAdmin;
use App\Entity\QuestionUser;
use App\Entity\Ticket;
use App\Entity\Transport;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Contrat des entités : chaque setter est relu à l'identique par son getter,
 * chaque add*() / remove*() maintient la collection (et le côté propriétaire).
 */
final class EntityAccessorsTest extends TestCase
{
    /** Setters dont la relecture n'est volontairement pas identique (testés à part). */
    private const SKIPPED = [
        User::class.'::setRoles',
    ];

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function entities(): iterable
    {
        foreach ([Category::class, City::class, Event::class, EventGroup::class, Localisation::class, Participation::class,
            Picture::class, QuestionAdmin::class, QuestionUser::class, Ticket::class, Transport::class, User::class] as $class) {
            yield (new \ReflectionClass($class))->getShortName() => [$class];
        }
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('entities')]
    public function testSettersRoundTrip(string $class): void
    {
        $entity = new $class();
        $checked = 0;

        foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $setter) {
            if (!str_starts_with($setter->getName(), 'set') || 1 !== $setter->getNumberOfParameters()
                || \in_array($class.'::'.$setter->getName(), self::SKIPPED, true)) {
                continue;
            }
            $getter = $this->findGetter($entity, substr($setter->getName(), 3));
            if (null === $getter) {
                continue;
            }

            $value = $this->sampleFor($setter->getParameters()[0]->getType());
            $returned = $setter->invoke($entity, $value);

            if ($returned instanceof $class) {
                self::assertSame($entity, $returned, $setter->getName().' doit être fluide');
            }
            self::assertEquals($value, $entity->{$getter}(), $class.'::'.$setter->getName());
            ++$checked;
        }

        self::assertGreaterThan(0, $checked);
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('entities')]
    public function testCollectionsAddAndRemove(string $class): void
    {
        $entity = new $class();
        $reflection = new \ReflectionClass($class);
        $checked = 0;

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $adder) {
            if (!str_starts_with($adder->getName(), 'add') || 1 !== $adder->getNumberOfParameters()) {
                continue;
            }
            $name = substr($adder->getName(), 3);
            $type = $adder->getParameters()[0]->getType();
            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin() || !$reflection->hasMethod('remove'.$name)) {
                continue;
            }
            $getter = $this->collectionGetter($entity, $name);
            /** @var class-string $itemClass */
            $itemClass = $type->getName();
            $item = new $itemClass();

            $entity->{$adder->getName()}($item);
            $entity->{$adder->getName()}($item); // idempotent
            self::assertCount(1, $entity->{$getter}(), $class.'::'.$adder->getName());

            $entity->{'remove'.$name}($item);
            self::assertCount(0, $entity->{$getter}(), $class.'::remove'.$name);
            ++$checked;
        }

        if (0 === $checked) {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testUserRolesAlwaysContainRoleUser(): void
    {
        $user = (new User())->setRoles(['ROLE_ORGANIZER', 'ROLE_USER']);

        self::assertSame(['ROLE_ORGANIZER', 'ROLE_USER'], $user->getRoles());
        self::assertSame(['ROLE_USER'], (new User())->getRoles());
        self::assertTrue($user->isOrganizer());
        self::assertFalse((new User())->isOrganizer());
    }

    private function findGetter(object $entity, string $property): ?string
    {
        foreach (['get'.$property, 'is'.$property, 'get'.lcfirst($property)] as $candidate) {
            if (method_exists($entity, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function collectionGetter(object $entity, string $singular): string
    {
        foreach (['get'.$singular.'s', 'get'.$singular.'es', 'get'.$singular] as $candidate) {
            if (method_exists($entity, $candidate) && $entity->{$candidate}() instanceof Collection) {
                return $candidate;
            }
        }

        self::fail('Getter de collection introuvable pour '.$singular);
    }

    private function sampleFor(?\ReflectionType $type): mixed
    {
        if (!$type instanceof \ReflectionNamedType) {
            return 'valeur';
        }

        return match ($type->getName()) {
            'string' => 'valeur',
            'int' => 42,
            'float' => 4.2,
            'bool' => true,
            'array' => ['a', 'b'],
            \DateTimeInterface::class, \DateTime::class => new \DateTime('2026-01-02 03:04:05'),
            \DateTimeImmutable::class => new \DateTimeImmutable('2026-01-02 03:04:05'),
            default => new ($type->getName())(),
        };
    }
}
