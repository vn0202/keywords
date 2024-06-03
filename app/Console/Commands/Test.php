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

    public function handle()
    {

        $keyword = Keyword::find(1);
        $query = new MoreLikeThisQuery(
            $keyword->keyword,
            [
                'fields' => ['refine_word'],
                'min_term_freq' => 1,
                'max_query_terms' => 12,
            ]
        );

        $list_keywords  = Keyword::search($query)->get();
        $unsearched_keywords = $list_keywords->where('status_search', "!=", 1)->select(['id', 'keyword'])->toArray();
        dd($unsearched_keywords);


    }
}
