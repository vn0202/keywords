<?php

namespace App\Services\SearxService;

use App\Models\Keyword;
use App\Services\SearxService\Data\NaturalResults;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;

class SearxClient
{
    protected string $host;

    protected string $token;

    protected Client $client;

    public static array $guzzle_options = [];

    /**
     * ClientAbstract constructor.
     *
     * @param string $host
     * @param string $token
     */
    public function __construct(string $host = '', string $token = '')
    {
        $this->host = $host ?: config('searx.host');
        $this->token = $token ?: config('searx.token');

        $guzzle_config = array_merge(
            [
                'base_uri' => $this->host,
                'headers' => [
                    'Accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'verify' => false,
            ],
            static::$guzzle_options,
        );
        $this->client = new Client($guzzle_config);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }


    public function search_concurrent(string $engine = '',array $queries, int $page = 1, int $per_page = 100,)
    {
        dump($queries);
        $retry = 3;

        $results = [];

        $requests = function ($total) use ($queries,$engine,$page, $per_page){
            $uri = '/search';
            for ($i = 0; $i < $total; $i++) {

                     yield function()  use ($uri,$queries,$engine,$page, $per_page, $i) {

                         return $this->client->getAsync($uri,[
                             'query' => [
                                 'engine' => $engine,
                                 'query' => $queries[$i]['keyword'],
                                 'page' => $page,
                                 'per_page' => $per_page
                             ]
                         ]);
                     };

            }
        };
        start:
        try {
            $pool = new Pool($this->client, $requests(count($queries)), [
                'concurrency' => 1,
                'fulfilled' => function (Response $response, $index) use (&$results, $queries) {

                    $response = Utils::jsonDecode($response->getBody()->getContents(), true);
                    $natural_result = NaturalResults::from($response);
                    if ($natural_result->search_metadata->status == "ERROR") {
                        throw new SearxException($natural_result->search_metadata->message);
                    }
                    $urls = $natural_result->organic_results->toCollection()->map(function ($items){
                        return $items->url;
                    });
                    $results[$queries[$index]['id']] = $urls->toArray();


                },
                'rejected' => function (RequestException $reason, $index) use (&$results, $queries) {
                    $results[$queries[$index]['id']] = null;
                },
            ]);
            $promise = $pool->promise();

            $promise->wait();
dd($results);
            return $results;
        }catch (\Exception $e)
        {
            if ($retry) {
                $retry--;
                goto start;
            }
            throw  $e;
        }

    }
    public function search(string $engine = '', string $query = '', int $page = 1, int $per_page = 100) {
        $response = $this->client->request('GET', '/search', [
            'query' => [
                'engine' => $engine,
                'query' => $query,
                'page' => $page,
                'per_page' => $per_page
            ]
        ]);
        $response = Utils::jsonDecode($response->getBody()->getContents(), true);
        $natural_result = NaturalResults::from($response);
        if ($natural_result->search_metadata->status == "ERROR") {
            throw new SearxException($natural_result->search_metadata->message);
        }

        return $natural_result;
    }

    public function searchWithRetry(string $engine = '', string $query = '', int $page = 1, int $per_page = 100, $retry = 5) {
        start:
        try {
            return $this->search($engine, $query, $page, $per_page);
        } catch (RequestException|SearxException $exception) {
            if ($retry) {
                $retry--;
                goto start;
            }

            throw $exception;
        }

    }
}
