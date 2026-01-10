<?php

namespace App\Model;

class RecetaDTO
{
    public function __construct(
        public int $id,
        public string $titulo,
        public int $comensales,
        public TipoRecetaDTO $tipo,
        public array $ingredientes = [],
        public array $pasos = [],
        public array $valoresNutritivos = []
    ) {}
}
