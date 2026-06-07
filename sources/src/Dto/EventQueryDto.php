<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventQueryDto
{
    public function __construct(
        #[Assert\Positive]
        #[Assert\LessThanOrEqual(100)]
        public int $limit = 12,
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $order = 'ASC',
        public ?array $categories = null,
        public ?string $localisation = null,
        public ?string $q = null,
    ) {
    }
}
