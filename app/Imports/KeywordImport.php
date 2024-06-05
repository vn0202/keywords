<?php

namespace App\Imports;

use App\Data\RawKeyWordData;
use App\Models\ImportFile;
use App\Models\Keyword;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class KeywordImport implements ToModel, WithChunkReading, WithStartRow, WithProgressBar, WithBatchInserts,WithUpserts
{
    use Importable;

    public function __construct(public ?ImportFile $file = null, public ?string $type = null)
    {
    }

    public function model(array $row)
    {
        $raw = RawKeyWordData::from(["keyword" => $row[0],"volume" => $row[1],"kd" => $row[2]]);
        return new Keyword([
            'keyword' => $row[0],
            "source" => $this->file->source,
            "country" => $this->file->country,
            "raw" => $raw,
            "type" => $this->type,

        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int{
        return 2;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'keyword';
    }
}
