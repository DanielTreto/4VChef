<?php

namespace App\Model;

class TipoNutrienteDTO
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $unidad
    ) {}
}
