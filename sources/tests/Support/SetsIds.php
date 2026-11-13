<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * Affecte un identifiant à une entité non persistée (tests unitaires).
 */
trait SetsIds
{
    /**
     * @template T of object
     *
     * @param T $entity
     *
     * @return T
     */
    protected static function withId(object $entity, int $id): object
    {
        (new \ReflectionProperty($entity, 'id'))->setValue($entity, $id);

        return $entity;
    }
}
