<?php

namespace App\Model;

class PasoDTO
{
    public function __construct(
        public int $order,
        public string $description
    ) {}
}
