<?php

namespace App\Orchid\Screens\File;

use App\Models\ImportFile;
use Illuminate\Validation\Rules\In;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class FileEditScreen extends Screen
{

    public ?ImportFile $file = null;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(ImportFile $file): iterable
    {
        return [
            'file' => $file,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->file->exists ? " Edit File" : "Create a File";
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


        ];
    }
}
