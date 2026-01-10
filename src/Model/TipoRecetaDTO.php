<?php

namespace App\Model;

class TipoRecetaDTO
{
    public function __construct(
        public string $nombre,
        public string $descripcion
    ) {}
}
