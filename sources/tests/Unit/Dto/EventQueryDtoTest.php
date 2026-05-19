<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\EventQueryDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EventQueryDtoTest extends TestCase
{
    private function validator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testDefaultsAreValid(): void
    {
        self::assertCount(0, $this->validator()->validate(new EventQueryDto()));
    }

    public function testRejectsInvalidOrder(): void
    {
        $violations = $this->validator()->validate(new EventQueryDto(order: 'DROP TABLE'));

        self::assertGreaterThan(0, $violations->count());
    }

    public function testRejectsNonPositivePageAndOversizedLimit(): void
    {
        self::assertGreaterThan(0, $this->validator()->validate(new EventQueryDto(page: 0))->count());
        self::assertGreaterThan(0, $this->validator()->validate(new EventQueryDto(limit: 5000))->count());
    }

    public function testAcceptsDescOrder(): void
    {
        self::assertCount(0, $this->validator()->validate(new EventQueryDto(order: 'DESC')));
    }
}
