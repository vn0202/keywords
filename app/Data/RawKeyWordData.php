<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RawKeyWordData extends  Data
{

    public function __construct(
        public string $keyword,
        public string $volume,
        public float $kd,
    )
    {
    }
}
