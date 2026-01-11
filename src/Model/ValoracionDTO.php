<?php

namespace App\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ValoracionDTO
{
    public function __construct(
        #[SerializedName('number-votes')]
        public int $numberVotes,
        #[SerializedName('rating-avg')]
        public float $ratingAvg
    ) {}
}
