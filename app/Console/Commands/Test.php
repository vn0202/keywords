<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\SearxService\SearxClient;
use Illuminate\Console\Command;
use ONGR\ElasticsearchDSL\Query\Specialized\MoreLikeThisQuery;

class Test extends  Command
{

    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function check_associate_array($array)
    {
        foreach ($array as $a)
        {if(is_array($a))
        {
            return true;
        }}
        return  false;
    }
    public function handle()
    {

        $list = Keyword::whereIn('id', [1,2])->get();

        Keyword::where('status_search', 1)
            ->where('duplicate_id', 0)
            ->chunkById(100, function ($collections){
                foreach ($collections as $keyword)
                {
                    $simlilarly = $this->getSimilarlyKeyword($keyword);



                }


        });

    }

    public function checkSimilarly(Keyword $keyword1, Keyword $keyword2, $thresold = 3)
    {
        $search_result_kw1 = $keyword1->meta->search_url ?? [];
        $search_result_kw2 = $keyword2->meta->search_url ?? [];

        if (count(array_intersect($search_result_kw1, $search_result_kw2)) > 3) {
            $similarly = $keyword1->raw->volume < $keyword2->raw->volume ? $keyword1 : $keyword2;
            $be_duplicated = $keyword1->raw->volume < $keyword1->raw->volume ? $keyword1 : $keyword1;

        }
        return false;

    }
    protected function getSimilarlyKeyword(Keyword $keyword)
    {
        $query = new MoreLikeThisQuery(
            $keyword->refine_word,
            [
                'fields' => ['refine_word'],
                'min_term_freq' => 1,
                'max_query_terms' => 12,
            ]
        );


        $results =  Keyword::search($query)->where('duplicate_id', '0')->get();
        if($results->contains($keyword))
        {
            $results = $results->filter(function ($item) use($keyword){
                return $item->id != $keyword->id;
            });
        }
        return $results;

    }
}
