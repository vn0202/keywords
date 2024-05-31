<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\SearxService\SearxClient;
use Elastic\Elasticsearch\Transport\Adapter\Guzzle;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use ONGR\ElasticsearchDSL\Query\Specialized\MoreLikeThisQuery;

class CheckDuplicate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-duplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $duplicated_list = [];
        Keyword::whereNotIn('id', $duplicated_list)->chunkById(100, function ($keywords ) use (&$duplicated_list){

            foreach ($keywords as $keyword)
            {
                $this->info("Start with: ". $keyword->keyword);
                $query = new MoreLikeThisQuery(
                    $keyword->keyword,
                    [
                        'fields' => ['refine_word'],
                        'min_term_freq' => 1,
                        'max_query_terms' => 12,
                    ]
                );
                $current_search_from_gg = $keyword->meta->search_url;
                if(!$current_search_from_gg)
                {
                    $current_search_from_gg = $this->search_result($keyword);
                }
                $meta = $keyword->meta;
                $meta->search_url = $current_search_from_gg;
                $keyword->meta = $meta;
                $keyword->status_search = 1;
                $keyword->save();

                $similarly  = Keyword::search($query)->orderBy('id', 'ASC')->get();
                $similarly->shift();
                foreach ($similarly->all() as $key)
                {
                    $this->info("Search key: ". $key->keyword);
                   $search_from_gg = $this->search_result($key);
                    $meta = $key->meta;
                    $meta->search_url = $search_from_gg;
                    $key->meta = $meta;
                    $key->status_search = 1;
                    if($search_from_gg->diff($current_search_from_gg)->count() > 3)
                    {
                        $duplicated = $keyword->raw->volume < $key->raw->volume ? $keyword : $key;
                        $be_duplicated =  $keyword->raw->volume < $key->raw->volume ? $key : $keyword;
                        $duplicated->duplicated_id = $be_duplicated->id;
                        $duplicated->save();
                        $duplicated_list[] = $duplicated->id;
                        $this->info($be_duplicated->keyword ." is duplicated with: ". $duplicated->id);
                    }

                    $key->save();
                }
            }
        });


    }

    public function search_result(Keyword $keyword, string $engine ='google', int $per_page=10)
    {
        $search = new SearxClient();
        $results = $search->search($engine, query: $keyword->keyword, per_page: $per_page);
        $urls = $results->organic_results->toCollection()->map(function ($item) {
            return $item->url;
        });
        return $urls ?: [];
    }
}
