<?php

namespace App\Imports;

use App\Models\JenisIzin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JenisIzinImport implements ToModel, WithHeadingRow
{
    protected $jenisizin;

    public function model(array $row)
    {

        $this->jenisizin = $row['jenisizin'];

        JenisIzin::updateOrInsert(
            ['jenisizin' => $this->jenisizin],
        );
    }

    public function getDateJenisIzin()
    {
        return $this->jenisizin;
    }
}
