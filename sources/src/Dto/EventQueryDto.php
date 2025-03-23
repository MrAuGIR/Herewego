<?php

namespace App\Dto;

class EventQueryDto
{
    public function __construct(
        public int $page,
        public string $order,
        public array $categories,
        public string $localisation,
        public string $q
    ) {
    }
}
