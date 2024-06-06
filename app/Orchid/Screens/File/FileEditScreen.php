<?php

namespace App\Orchid\Screens\File;

use App\Models\ImportFile;
use Illuminate\Support\Facades\Request;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
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
        return [
            Button::make("upload")
            ->method("upload")
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

            Layout::rows([
                Upload::make('file')->title("Select a file"),
                Input::make('name'),
                Input::make('source')->title("Source")->required(),
                Input::make("country")->title("Country")->required(),
            ])

        ];
    }

    public function upload(\Illuminate\Http\Request $request){

        $this->file->source = $request->source;
        $this->file->country = $request->country;
        if($request->name){
            $this->file->name = $request->name;

        }else{
           $this->file->name = Attachment::find(\Arr::get($request->file, 0 ))->original_name;
        }
        $this->file->save();
        $this->file->attachment()->sync($request->file);


        Alert::info("Create successfully!");
    }

}
