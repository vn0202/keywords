<?php

namespace App\Models;

use App\Data\KeyWordMetaData;
use App\Data\POSTaggingData;
use App\Data\RawKeyWordData;
use App\Data\Stopwords;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Keyword extends Model
{
    use HasFactory, Sluggable, Searchable;
    protected  $guarded = [];

    protected $casts = [
        'raw' => RawKeyWordData::class,
        'meta' => KeyWordMetaData::class,
    ];


    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }


    public function toSearchableArray():array
    {

        $data = $this->toArray();
        $pos = $this->meta->pos;
         $refine_word = [];
        foreach ($pos as $part)
        {

            if(!in_array($part->word, Stopwords::STOPWORDS))
            {
                $refine_word[] = $part->word;
            }
        }
        $data['refine_word'] = implode(' ',$refine_word);

        return \Arr::only($data, ['id', 'refine_word']);
    }

}
