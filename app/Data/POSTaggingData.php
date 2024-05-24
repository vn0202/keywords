<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class POSTaggingData extends  Data
{

    public function __construct(
        public string $word,
        public int $order ,
    ){

    }


}
