<?php

namespace App\Imports;

use App\Models\Holiday;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HolidayImport implements ToModel, WithHeadingRow
{
    protected $dateHoliday;
    protected $noteHoliday;

    public function model(array $row)
    {

        $this->dateHoliday = $row['date_holiday'];
        $this->noteHoliday = $row['note_holiday'];

        Holiday::updateOrInsert(
            ['date' => $this->dateHoliday],
            ['note' => $this->noteHoliday, 'updated_at' => now()]
        );
    }

    public function getDateHoliday()
    {
        return $this->dateHoliday;
    }

    public function getNoteHoliday()
    {
        return $this->noteHoliday;
    }
}
