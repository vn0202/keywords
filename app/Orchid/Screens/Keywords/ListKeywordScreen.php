<?php

namespace App\Orchid\Screens\Keywords;

use App\Models\Keyword;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ListKeywordScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            "keywords" => Keyword::simplePaginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'ListKeywordScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('keywords', [
                TD::make('id'),
                TD::make('keyword'),
                TD::make('type'),
                TD::make('info')
                ->render(function (Keyword $keyword){
                    return $keyword->country . "<br>" . $keyword->source;
                }),
                TD::make("raw")
                ->render(function (Keyword $keyword){
                    return "Volumn: ". $keyword->raw->volume . "<br>".
                        "KD: ". $keyword->raw->kd;
                }),
                TD::make('status_search'),
                TD::make('duplicate_id'),
                TD::make("Similarly Keyword")
                ->render(function (Keyword $keyword){
                    $similarlies = $keyword->beDuplicated;
//                    dd($similarlies->select('id', "keyword")->toArray());
                    $html = "";
                    foreach ($similarlies as $key)
                    {
                        $html .= $key->keyword . "<br>";
                    }
                    return $html;

                }),
                TD::make("created_at")->render(function (Keyword $keyword){
                    return $keyword->created_at->diffForHumans();
                })
            ])
        ];
    }
}
