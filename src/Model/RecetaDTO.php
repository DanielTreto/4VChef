<?php

namespace App\Model;

class RecetaDTO
{
    public function __construct(
        public string $titulo,
        public int $comensales,
        public array $tipos = [],
        public array $ingredientes = [],
        public array $pasos = [],
        public array $valoresNutritivos = []
    ) {}
}
