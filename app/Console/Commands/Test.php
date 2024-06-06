<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\SearxService\SearxClient;
use Illuminate\Console\Command;
use ONGR\ElasticsearchDSL\Query\Specialized\MoreLikeThisQuery;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Test extends  Command
{

    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function handle()
    {

dd(basename('keyword_bai_hat_vi-vn.xlsx'));
        $list_duplicate = [];
        Keyword::where('status_search', 1)
            ->where('duplicate_id', 0)
            ->chunkById(100, function ($collections) use (&$list_duplicate){
                foreach ($collections as $keyword)
                {
                    if(in_array($keyword->id, $list_duplicate))
                    {
                        continue;
                    }
                    $list_similar_key = $this->getSimilarlyKeyword($keyword, $list_duplicate);

                    foreach ($list_similar_key as $key)
                    {
                        if($key->id == $keyword->id )
                            continue;
                        if($this->checkSimilarly($keyword, $key)){
                            $original_keyword  = $keyword->raw->volume > $key->raw->volume ? $keyword : $key;
                            $similar_key = $keyword->raw->volume < $key->raw->volume ? $keyword : $key;
                            $similar_key->duplicate_id = $original_keyword->id;
                            $list_duplicate[] = $similar_key->id;
                            $similar_key->save();

                            $this->info("The {$similar_key->keyword}[{$similar_key->id}] is similar with  {$original_keyword->keyword}[{$original_keyword->id}]");
                        }
                    }

                }

        });

    }

    public function checkSimilarly(Keyword $keyword1, Keyword $keyword2, $thresold = 3):bool
    {
        $search_result_kw1 = $keyword1->meta->search_url ?? [];
        $search_result_kw2 = $keyword2->meta->search_url ?? [];

        if (count(array_intersect(array_slice($search_result_kw1, 0,10), array_slice($search_result_kw2,0 ,10))) > $thresold) {
            return true;
        }
        return false;

    }
    protected function getSimilarlyKeyword(Keyword $keyword, $list_duplicate = [])
    {
        $query = new MoreLikeThisQuery(
            $keyword->refine_word,
            [
                'fields' => ['refine_word'],
                'min_term_freq' => 1,
                'max_query_terms' => 12,
            ]
        );
        $results =  Keyword::search($query)
            ->where('duplicate_id', 0)
            ->get();

            $results = $results->filter(function ($item) use($keyword, $list_duplicate){
                return $item->id != $keyword->id  && !in_array($item->id, $list_duplicate);
            });

        return $results;

    }
}
