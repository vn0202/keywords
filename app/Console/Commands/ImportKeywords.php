<?php

namespace App\Console\Commands;

use App\Imports\KeywordImport;
use App\Models\ImportFile;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportKeywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import {id : id of file}';

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
        $this->output->title('Starting import');
        $id = $this->argument('id');
        $file = ImportFile::findOrFail($id);
        (new KeywordImport($file, "book"))->withOutput($this->output)->import($file->path);


        Excel::import(new KeywordImport($file), $file->path);






    }
}
