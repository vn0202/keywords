<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use Elastic\Elasticsearch\Transport\Adapter\Guzzle;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
        $client = new Client();
       $response =  $client->post('http://localhost:9200/postag_keywords_1716545723/_search', [
            "headers" => [
                "Content-type" => "Application/json",
            ],
            'json' => [
                "query" => [
                    "more_like_this" => [
                        "fields" => ["refine_word"],
                        "like" => [
                            [
                                "_index" => "postag_keywords_1716545723",
                                "_id" => "1"
                            ]
                        ],
                        "min_term_freq" => 1,
                        "max_query_terms" => 2
                    ]
                ]
            ]

        ]);
       dd(json_decode($response->getBody()->getContents()));
    }
}
