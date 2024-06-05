<?php

namespace App\Orchid\Screens\File;

use App\Models\ImportFile;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ListFileScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'files' => ImportFile::paginate(10)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'List Import File';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('add')->icon('plus')
            ->route("platform.files.add")
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('files', [
                TD::make('id'),
                TD::make('namespace'),
                TD::make('source'),
                TD::make('country'),
                TD::make('created_at')
                ->render(function ($file){
                    return $file->created_at->diffForHumans();
                })

            ])
        ];
    }
}
