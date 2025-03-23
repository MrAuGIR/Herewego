<?php

namespace App\Dto;

class EventQueryDto
{
    public function __construct(
        public int $limit = 12,
        public int $page = 1,
        public string $order = 'ASC',
        public ?array $categories = null,
        public ?string $localisation = null,
        public ?string $q = null
    ) {
    }
}
