<?php

namespace App\Model;

class NutrienteDTO
{
    public function __construct(
        public string $nombre,
        public string $unidad
    ) {}
}
