<?php

namespace App\Models;

use App\Data\RawKeyWordData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;

    protected $casts = [
        'raw' => RawKeyWordData::class,
    ];
}
