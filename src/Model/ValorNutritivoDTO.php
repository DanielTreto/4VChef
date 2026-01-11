<?php

namespace App\Model;

class ValorNutritivoDTO
{
    public function __construct(
        public TipoNutrienteDTO $type,
        public float $quantity,
    ) {}
}
