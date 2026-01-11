<?php

namespace App\Model;

class RecetaDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public int $numberDiner,
        public TipoRecetaDTO $type,
        public array $ingredients = [],
        public array $steps = [],
        public array $nutrients = [],
        public ?ValoracionDTO $rating = null
    ) {}
}
