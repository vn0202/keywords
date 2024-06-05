<?php

namespace App\Models;

use App\Data\KeyWordMetaData;
use App\Data\POSTaggingData;
use App\Data\RawKeyWordData;
use App\Data\Stopwords;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Orchid\Screen\AsSource;

class Keyword extends Model
{
    use HasFactory, Sluggable, Searchable, AsSource;
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


    public function shouldBeSearchable()
    {
        return true;
    }

    public function beDuplicated(): HasMany{
        return $this->hasMany(self::class, 'duplicate_id', 'id');
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

        return \Arr::only($data, ['id', 'refine_word', 'status_search', 'keyword']);
    }


    public function getRefineWordAttribute()
    {
        $pos = $this->meta->pos;
        $refine_word = [];
        foreach ($pos as $part)
        {

            if(!in_array($part->word, Stopwords::STOPWORDS))
            {
                $refine_word[] = $part->word;
            }
        }
        return implode(' ',$refine_word);
    }

    public function getDuplicatedAttribute()
    {
        return $this->beDuplicated->pluck('keyword')->toArray();
    }


}
