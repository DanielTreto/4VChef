<?php

namespace App\Model;

class IngredienteDTO
{
    public function __construct(
        public string $nombre,
        public float $cantidad,
        public string $unidad
    ) {}
}
