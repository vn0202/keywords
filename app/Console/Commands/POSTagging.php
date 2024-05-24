<?php

namespace App\Console\Commands;


use App\Data\POSTaggingData;
use App\Models\Keyword;
use App\Services\AI\Enum\AiModelEnum;
use App\Services\AI\Gemini\Sessions\GeminiSession;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class POSTagging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pos
    {--chunk=10 : limit the number of keyword to POS  }
    {--preview: only view not save DB } ';

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

        Keyword::chunkById(100, function ($keywords) use ($client){
            /** @var Keyword $keyword */
            foreach ($keywords as $keyword)
            {
                $response = $client->get("http://localhost:1880/tokenize", [
                    'query' => ['text' => $keyword->keyword ],
                ]);
                if($response->getStatusCode() == 200)
                {
                    $this->info("POS tagging keyword: ". $keyword->keyword);

                    $response = json_decode($response->getBody()->getContents(), true);
                    $meta['pos'] =[];

                    foreach ($response as $key => $item)
                    {
                        $meta['pos'][] = POSTaggingData::from(['word' => $item, 'order' => $key]);
                    }
                    $keyword->meta = $meta;
                    $keyword->save();
                    $this->info("POS tagging keyword successfully!");

                }
            }

        });

    }
}
