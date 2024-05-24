<?php

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class KeyWordMetaData extends  Data
{

    public function __construct(
        /** @var Collection<int, POSTaggingData> */
        public ?Collection $pos = null
    )
    {
    }
}
