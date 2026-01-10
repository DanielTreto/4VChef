<?php

namespace App\Model;

class ValorNutritivoDTO
{
    public function __construct(
        public NutrienteDTO $nutriente,
        public float $cantidad,
    ) {}
}
