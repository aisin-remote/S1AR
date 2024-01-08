<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HolidayImport;
use Yajra\DataTables\DataTables;
use App\Models\Holiday;
use Carbon\Carbon;

use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        return view('masterHoliday');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|mimes:xlsx,xls|max:10240', // Maksimal 10 MB
        ]);

        $documentPath = $request->file('document')->storeAs('public/', 'import_holiday.xlsx');

        $import = new HolidayImport;
        Excel::import($import, storage_path('app/' . $documentPath));

        // Ambil nilai date_holiday dan note_holiday
        $dateHoliday = $import->getDateHoliday();
        $noteHoliday = $import->getNoteHoliday();

        return redirect()->route('master-holiday')->with('success', 'Dokumen berhasil diunggah.');
    }

    public function getDataHoliday()
    {
        $twoYearsAgo = Carbon::now()->year;
        $holidays = Holiday::where('date','like', $twoYearsAgo . '%')->select(['date', 'note'])->get();

        // Gunakan DataTables untuk memformat data
        return DataTables::of($holidays)
            ->make(true);
    }
}
