<?php

namespace App\Model;

class ValoracionDTO
{
    public function __construct(
        public int $numberVotes,
        public float $ratingAvg
    ) {}
}
