<?php

namespace App\Model;

class IngredienteDTO
{
    public function __construct(
        public string $name,
        public float $quantity,
        public string $unit
    ) {}
}
