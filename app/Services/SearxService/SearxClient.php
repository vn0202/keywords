<?php

namespace App\Services\SearxService;

use App\Services\SearxService\Data\NaturalResults;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;
use GuzzleHttp\Promise;

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

    public function searchMultiple(string $engine = '', array $keywords = [], int $page = 1, int $per_page = 12)
    {
        $promises = [];
        foreach ($keywords as $keyword){
            $promises[$keyword['id']] = $this->searchAsyncWithRetry($engine, $keyword['keyword'], $page, $per_page);
        }
        $responses = Promise\Utils::settle($promises)->wait();
        $results = [];
        foreach ($responses as $id => $response) {
            if (array_key_exists('value', $response)){
                $response = Utils::jsonDecode($response['value']->getBody()->getContents(), true);
                $natural_result = NaturalResults::from($response);
                if ($natural_result->organic_results){
                    $results[$id] = $natural_result->organic_results->toCollection()->map(function ($item)
                    {
                        return $item->url;
                    })->toArray();
                }else{
                    $results[$id] = [];
                }
            }else{
                $results[$id] = [];
            }
        }

        return $results;
    }

    public function multi_search(string $engine = '',array $queries, int $page = 1, int $per_page = 100,$retry = 5)
    {
        $results = [];
        $promises = [];
        foreach ($queries as  $query)
        {
           $promises[$query['id']] = $this->searchAsyncWithRetry("google", $query['keyword'], $page, $per_page, $retry);
        }

           $responses =  Promise\Utils::settle($promises)->wait();

            foreach ($responses as $key => $respon)
            {
                if(array_key_exists('value', $respon))
                {

                        $response = Utils::jsonDecode($respon['value']->getBody()->getContents(), true);

                        $natural_result = NaturalResults::from($response);
                        if ($natural_result->search_metadata->status == "ERROR") {
                            throw new SearxException($natural_result->search_metadata->message);
                        }
                        $urls = $natural_result->organic_results->toCollection()->map(function ($items){
                            return $items->url;
                        });
                        $results[$key] = $urls->toArray();
                    }
            }

            return $results;

    }
    public function search_concurrent(string $engine = '',array $queries, int $page = 1, int $per_page = 100,)
    {
        $retry = 5;

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
            return $results;
        }catch (\Exception $e)
        {
            if ($retry) {
                $retry--;

                sleep(3);
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
                dump("This is $retry time");
                goto start;
            }

            throw $exception;
        }

    }

    public function searchAsyncWithRetry(string $engine = '', string $query = '', int $page = 1, int $per_page = 100, $retry = 5) {
        start:
        try {
            return $this->searchAsync($engine, $query, $page, $per_page);
        } catch (RequestException|SearxException $exception) {
            if ($retry) {
                $retry--;
                dump("This retry $retry");
                goto start;
            }

            throw $exception;
        }

    }
    public function searchAsync(string $engine = '', string $query = '', int $page = 1, int $per_page = 12)
    {
        return $this->client->getAsync('/search', [
            'query' => [
                'engine' => $engine,
                'query' => $query,
                'page' => $page,
                'per_page' => $per_page
            ]
        ]);
    }
}
