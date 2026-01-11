<?php

namespace App\Model;

class ValoracionDTO
{
    public function __construct(
        public int $id,
        public int $idReceta,
        public int $calificacion,
    ) {}
}
