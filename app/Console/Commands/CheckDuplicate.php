<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Services\SearxService\SearxClient;
use Elastic\Elasticsearch\Transport\Adapter\Guzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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

        $duplicated_list = Keyword::where('duplicate_id', '!=', 0)->select('id')->pluck('id')->toArray();
        Keyword::whereNotIn('id', $duplicated_list)->chunkById(100, function ($keywords) use (&$duplicated_list) {

            foreach ($keywords as $keyword) {
                if (in_array($keyword->id, $duplicated_list)) {
                    continue;
                }

                $this->info("Start with: " . $keyword->keyword);
                $list_keywords = $this->getSimilarlyKeyword($keyword);

                $list_keywords = $list_keywords->when(!$list_keywords->contains($keyword), function (Collection $list_keywords) use ($keyword) {
                    $list_keywords->push($keyword);
                });

                $results = $this->getSearchResults($list_keywords);
                $check_keyword_results = $results[$keyword->id];
                $check_keyword = $keyword;
                foreach ($results as $key => $value) {
                    if ($key == $keyword->id) {
                        continue;
                    }
                    $_keyword = Keyword::find($key);
                    $meta = $_keyword->meta;
                    $meta->search_url = $value;
                    $_keyword->status_search = 1;
                    $_keyword->save();
                    if(!$value || !$check_keyword_results)
                    {
                        continue;
                    }
                    if (count(array_intersect($check_keyword_results, $value)) > 3) {
                        $duplicated = $check_keyword->raw->volume < $_keyword->raw->volume ? $check_keyword : $_keyword;
                        $be_duplicated = $check_keyword->raw->volume < $_keyword->raw->volume ? $_keyword : $check_keyword;
                        $duplicated->duplicate_id = $be_duplicated->id;
                        $duplicated->save();
                        $duplicated_list[] = $duplicated->id;
                        $this->error($duplicated->keyword . "[$duplicated->id]" . " is similarly with: " . $be_duplicated->id);
                    }
                }

            }
        });


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


        return Keyword::search($query)->get();
    }


    protected function getSearchResults($list_keywords)
    {
        $un_searched_keywords = $list_keywords->where('status_search', "!=", 1)->select('id', 'keyword')->toArray();
        $tmp = [];
        foreach ($un_searched_keywords as  $v) {
            $tmp[] = $v;
        }
        $un_searched_keywords = $tmp;
        $searched_url = $list_keywords->where('status_search', "=", 1);
        $searched_results = [];
        $searched_url->each(function ($item) use (&$searched_results) {
            $searched_results[$item->id] = $item->meta->search_url;
        });
        if ($un_searched_keywords) {
            $search = new SearxClient();
            $results = $search->searchMultiple('google', $un_searched_keywords, per_page: 10);
        } else {
            $results = [];
        }
        $results += $searched_results;
        return $results;
    }


    public function search_result(Keyword $keyword, string $engine = 'google', int $per_page = 10)
    {
        $search = new SearxClient();
        $results = $search->searchWithRetry($engine, query: $keyword->keyword, per_page: $per_page, retry: 3);
        $urls = $results->organic_results->toCollection()->map(function ($item) {
            return $item->url;
        });
        return $urls ?: [];
    }

}
