<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RawKeyWordData extends  Data
{

    public function __construct(
        public string $keyword,
        public  $volume,
        public  $kd,
    )
    {
    }
}
