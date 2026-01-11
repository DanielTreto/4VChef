<?php

namespace App\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

class RecetaDTO
{
    public function __construct(
        public int $id,
        public string $title,
        #[SerializedName('number-diner')]
        public int $numberDiner,
        public TipoRecetaDTO $type,
        public array $ingredients = [],
        public array $steps = [],
        public array $nutrients = [],
        public ?ValoracionDTO $rating = null
    ) {}
}
