<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    public function index() {
        $tahunSekarang = Carbon::now()->year;
        $tanggalDuaHariSebelumnya = Carbon::now()->subDays(2)->format('Y-m-d');

        $data = DB::connection('sqlsrv')
            ->table('attdly1')
            ->select('attdly1.empno', 'attdly1.datin', 'attdly1.timin', 'attdly1.datot', 'attdly1.timot', 'pnmempl.empnm')
            ->join('pnmempl', 'attdly1.empno', '=', 'pnmempl.empno')
            ->whereYear('attdly1.datin', '=', $tahunSekarang) // Hanya data dari tahun sekarang
            ->where('attdly1.datin', '>=', $tanggalDuaHariSebelumnya) // Hanya data dari 3 bulan ke belakang
            ->orderBy('attdly1.datin', 'asc') // Mengurutkan data berdasarkan kolom datin secara ascending (naik)
            ->orderBy('attdly1.timin', 'asc') // Mengurutkan data berdasarkan kolom timin secara ascending (naik)
            ->get();

        // Mengubah format tanggal dan jam dalam hasil data
        foreach ($data as $row) {
            if ($row->datin != "        ") {
                $row->datin = substr($row->datin, 0, 4) . '-' . substr($row->datin, 4, 2) . '-' . substr($row->datin, 6, 2);
                $row->timin = substr($row->timin, 0, 2) . ':' . substr($row->timin, 2, 2);
            } else {
                $row->datin = "Tidak Ada Data";
                $row->timin = "Tidak Ada Data";
            }

            if ($row->datot != "        ") {
                $row->datot = substr($row->datot, 0, 4) . '-' . substr($row->datot, 4, 2) . '-' . substr($row->datot, 6, 2);
                $row->timot = substr($row->timot, 0, 2) . ':' . substr($row->timot, 2, 2);
            } else {
                $row->datot = "Tidak Ada Data";
                $row->timot = "Tidak Ada Data";
            }
        }

        return view('index');
    }

    public function getData()
    {
        $tahunSekarang = Carbon::now()->year;
        $tanggalDuaHariSebelumnya = Carbon::now()->subDays(2)->format('Y-m-d');

        $data = DB::connection('sqlsrv')
            ->table('attdly1')
            ->select('attdly1.empno', 'attdly1.datin', 'attdly1.timin', 'attdly1.datot', 'attdly1.timot', 'pnmempl.empnm')
            ->join('pnmempl', 'attdly1.empno', '=', 'pnmempl.empno')
            ->whereYear('attdly1.datin', '=', $tahunSekarang) // Hanya data dari tahun sekarang
            ->where('attdly1.datin', '>=', $tanggalDuaHariSebelumnya) // Hanya data dari 3 bulan ke belakang
            ->orderBy('attdly1.datin', 'asc') // Mengurutkan data berdasarkan kolom datin secara ascending (naik)
            ->orderBy('attdly1.timin', 'asc') // Mengurutkan data berdasarkan kolom timin secara ascending (naik)
            ->get();

        // Mengubah format tanggal dan jam dalam hasil data
        foreach ($data as $row) {
            if ($row->datin != "        ") {
                $row->datin = substr($row->datin, 0, 4) . '-' . substr($row->datin, 4, 2) . '-' . substr($row->datin, 6, 2);
                $row->timin = substr($row->timin, 0, 2) . ':' . substr($row->timin, 2, 2);
            } else {
                $row->datin = "Tidak Ada Data";
                $row->timin = "Tidak Ada Data";
            }

            if ($row->datot != "        ") {
                $row->datot = substr($row->datot, 0, 4) . '-' . substr($row->datot, 4, 2) . '-' . substr($row->datot, 6, 2);
                $row->timot = substr($row->timot, 0, 2) . ':' . substr($row->timot, 2, 2);
            } else {
                $row->datot = "Tidak Ada Data";
                $row->timot = "Tidak Ada Data";
            }
        }

        // dd($data);

        return DataTables::of($data)->make(true);
        // return view('index', ['data' => $data]);

        // $data = DB::connection('sqlsrv') // Sesuaikan nama koneksi database jika perlu
        //     ->table('dbo.attdly1')
        //     ->select('no')
        //     ->get();

        //     dd($data);

        // return view('welcome', ['data' => $data]);
    }
}
