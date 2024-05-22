<?php

namespace App\Imports;

use App\Data\RawKeyWordData;
use App\Models\ImportFile;
use App\Models\Keyword;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithStartRow;

class KeywordImport implements ToModel, WithChunkReading, WithStartRow, WithProgressBar
{
    use Importable;

    public function __construct(public ?ImportFile $file = null)
    {
    }

    public function model(array $row)
    {
        $raw = RawKeyWordData::from($row[0], $row[1], $row[2]);
        dd($raw);
        return new Keyword([
            'keyword' => $row[0],
            "source" => $this->file->source,
            "country" => $this->file->country,
            "raw" => $raw,

        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int{
        return 2;
    }

}
