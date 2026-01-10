<?php

namespace App\Model;

class ValorNutritivoDTO
{
    public function __construct(
        public TipoNutrienteDTO $nutriente,
        public float $cantidad,
    ) {}
}
