<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function getData()
    {
        $tahunSekarang = Carbon::now()->year;
        $tanggalSekarang = Carbon::now()->format('Ymd');

        $data = DB::connection('sqlsrv')
            ->table('attdly1')
            ->select('attdly1.empno', 'attdly1.datin', 'attdly1.timin', 'attdly1.datot', 'attdly1.timot', 'pnmempl.empnm')
            ->join('pnmempl', 'attdly1.empno', '=', 'pnmempl.empno')
            ->whereYear('attdly1.datin', '=', $tahunSekarang) // Hanya data dari tahun sekarang
            ->where('attdly1.datin', '>=', $tanggalSekarang) // Hanya data dari 2 hari kebelakang
            ->orderBy('attdly1.datin', 'asc') // Mengurutkan data berdasarkan kolom datin secara ascending (naik)
            ->orderBy('attdly1.timin', 'asc') // Mengurutkan data berdasarkan kolom timin secara ascending (naik)
            ->get();

        $data->map(function ($row) {
            $subSection = DB::connection('mysql3')
                ->table('m_employees')
                ->where(function ($query) use ($row) {
                    $query->where('npk', $row->empno)
                        ->orWhere('nama', 'LIKE', '%' . $row->empnm . '%');
                })
                ->value('sub_section');

            $occupation = DB::connection('mysql3')
                ->table('m_employees')
                ->where(function ($query) use ($row) {
                    $query->where('npk', $row->empno)
                        ->orWhere('nama', 'LIKE', '%' . $row->empnm . '%');
                })
                ->value('occupation');

            // Menambahkan kolom sub_section ke hasil data dari SQL Server
            $row->sub_section = $subSection ? $subSection : 'Tidak Ada Data'; // Jika sub_section tidak ada, beri nilai default
            $row->occupation = $occupation ? $occupation : 'Tidak Ada Data';

            if ($occupation == 'GMR') {
                $department = DB::connection('mysql3')
                    ->table('m_divisions')
                    ->where('code', $subSection)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } elseif ($occupation == 'KDP') {
                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $subSection)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } elseif ($occupation == 'SPV') {
                $codeDepartment = DB::connection('mysql3')
                    ->table('m_sections')
                    ->where('code', $subSection)
                    ->value('code_department');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->codeDepartment = $codeDepartment ? $codeDepartment : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $codeDepartment)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } else {
                $section = DB::connection('mysql3')
                    ->table('m_sub_sections')
                    ->where('code', $subSection)
                    ->value('code_section');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->section = $section ? $section : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $codeDepartment = DB::connection('mysql3')
                    ->table('m_sections')
                    ->where('code', $section)
                    ->value('code_department');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->codeDepartment = $codeDepartment ? $codeDepartment : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $codeDepartment)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            }

            return $row;
        });

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

        return DataTables::of($data)->make(true);
    }

    public function indexWeekly()
    {
        return view('weeklyAttendance');
    }

    public function getDataWeekly()
    {
        $tahunSekarang = Carbon::now()->year;
        $tanggalSekarang = Carbon::now()->subDays(7)->format('Ymd');
        $npk = auth()->user()->npk; // Ambil NPK dari user yang sedang login

        $data = DB::connection('sqlsrv')
            ->table('attdly1')
            ->select('attdly1.empno', 'attdly1.datin', 'attdly1.timin', 'attdly1.datot', 'attdly1.timot', 'pnmempl.empnm')
            ->join('pnmempl', 'attdly1.empno', '=', 'pnmempl.empno')
            ->whereYear('attdly1.datin', '=', $tahunSekarang) // Hanya data dari tahun sekarang
            ->where('attdly1.datin', '>=', $tanggalSekarang) // Hanya data dari 1 minggu kebelakang
            ->where('attdly1.empno', '=', $npk) // Hanya data dengan empno yang sesuai dengan NPK pengguna saat login
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

        return DataTables::of($data)->make(true);
    }

    public function getDataMonthly()
    {
        set_time_limit(300); // Mengatur batas waktu eksekusi menjadi 5 menit

        $tahunSekarang = Carbon::now()->year;
        $bulanSekarang = Carbon::now()->month;

        $data = DB::connection('sqlsrv')
            ->table('attdly1')
            ->select('attdly1.empno', 'pnmempl.empnm', 'attdly1.datin', 'atttrn2.rsccd', 'atttrn2.schdt')
            ->join('pnmempl', 'attdly1.empno', '=', 'pnmempl.empno')
            ->leftJoin('atttrn2', function ($join) {
                $join->on('attdly1.empno', '=', 'atttrn2.empno')
                    ->whereYear('atttrn2.schdt', '=', DB::raw('YEAR(attdly1.datin)'))
                    ->whereMonth('atttrn2.schdt', '=', DB::raw('MONTH(attdly1.datin)'));
            })
            ->whereYear('attdly1.datin', '=', $tahunSekarang)
            ->whereMonth('attdly1.datin', '=', $bulanSekarang)
            ->orderBy('attdly1.empno', 'asc')
            ->orderBy('attdly1.datin', 'asc')
            ->get();

        // Menggunakan map untuk menambahkan sub_section ke setiap baris data
        $data->map(function ($row) {
            $subSection = DB::connection('mysql3')
                ->table('m_employees')
                ->where(function ($query) use ($row) {
                    $query->where('npk', $row->empno)
                        ->orWhere('nama', 'LIKE', '%' . $row->empnm . '%');
                })
                ->value('sub_section');

            $occupation = DB::connection('mysql3')
                ->table('m_employees')
                ->where(function ($query) use ($row) {
                    $query->where('npk', $row->empno)
                        ->orWhere('nama', 'LIKE', '%' . $row->empnm . '%');
                })
                ->value('occupation');

            // Tambahkan informasi sub_section ke setiap baris data
            $row->sub_section = $subSection ? $subSection : 'Tidak Ada Data';

            $row->occupation = $occupation ? $occupation : 'Tidak Ada Data';

            if ($occupation == 'GMR') {
                $department = DB::connection('mysql3')
                    ->table('m_divisions')
                    ->where('code', $subSection)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } elseif ($occupation == 'KDP') {
                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $subSection)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } elseif ($occupation == 'SPV') {
                $codeDepartment = DB::connection('mysql3')
                    ->table('m_sections')
                    ->where('code', $subSection)
                    ->value('code_department');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->codeDepartment = $codeDepartment ? $codeDepartment : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $codeDepartment)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } else {
                $section = DB::connection('mysql3')
                    ->table('m_sub_sections')
                    ->where('code', $subSection)
                    ->value('code_section');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->section = $section ? $section : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $codeDepartment = DB::connection('mysql3')
                    ->table('m_sections')
                    ->where('code', $section)
                    ->value('code_department');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->codeDepartment = $codeDepartment ? $codeDepartment : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $codeDepartment)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            }

            return $row;
        });

        $groupedData = $data->groupBy('empno');

        // dd($groupedData);

        return view('monthlyAttendance', compact('groupedData'));
    }

    public function getDataMonthlyDepartment()
    {
        set_time_limit(300); // Mengatur batas waktu eksekusi menjadi 5 menit

        $tahunSekarang = Carbon::now()->year;
        $bulanSekarang = Carbon::now()->month;

        $loggedInNPK = auth()->user()->npk;

        $data = DB::connection('sqlsrv')
            ->table('attdly1')
            ->select('attdly1.empno', 'pnmempl.empnm', 'attdly1.datin', 'atttrn2.rsccd', 'atttrn2.schdt')
            ->join('pnmempl', 'attdly1.empno', '=', 'pnmempl.empno')
            ->leftJoin('atttrn2', function ($join) {
                $join->on('attdly1.empno', '=', 'atttrn2.empno')
                    ->whereYear('atttrn2.schdt', '=', DB::raw('YEAR(attdly1.datin)'))
                    ->whereMonth('atttrn2.schdt', '=', DB::raw('MONTH(attdly1.datin)'));
            })
            ->whereYear('attdly1.datin', '=', $tahunSekarang)
            ->whereMonth('attdly1.datin', '=', $bulanSekarang)
            ->orderBy('attdly1.empno', 'asc')
            ->orderBy('attdly1.datin', 'asc')
            ->get();

        // Menggunakan map untuk menambahkan sub_section ke setiap baris data
        $data->map(function ($row) {
            $subSection = DB::connection('mysql3')
                ->table('m_employees')
                ->where(function ($query) use ($row) {
                    $query->where('npk', $row->empno)
                        ->orWhere('nama', 'LIKE', '%' . $row->empnm . '%');
                })
                ->value('sub_section');

            $occupation = DB::connection('mysql3')
                ->table('m_employees')
                ->where(function ($query) use ($row) {
                    $query->where('npk', $row->empno)
                        ->orWhere('nama', 'LIKE', '%' . $row->empnm . '%');
                })
                ->value('occupation');

            // Tambahkan informasi sub_section ke setiap baris data
            $row->sub_section = $subSection ? $subSection : 'Tidak Ada Data';

            $row->occupation = $occupation ? $occupation : 'Tidak Ada Data';

            if ($occupation == 'GMR') {
                $department = DB::connection('mysql3')
                    ->table('m_divisions')
                    ->where('code', $subSection)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } elseif ($occupation == 'KDP') {
                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $subSection)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } elseif ($occupation == 'SPV') {
                $codeDepartment = DB::connection('mysql3')
                    ->table('m_sections')
                    ->where('code', $subSection)
                    ->value('code_department');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->codeDepartment = $codeDepartment ? $codeDepartment : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $codeDepartment)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            } else {
                $section = DB::connection('mysql3')
                    ->table('m_sub_sections')
                    ->where('code', $subSection)
                    ->value('code_section');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->section = $section ? $section : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $codeDepartment = DB::connection('mysql3')
                    ->table('m_sections')
                    ->where('code', $section)
                    ->value('code_department');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->codeDepartment = $codeDepartment ? $codeDepartment : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default

                $department = DB::connection('mysql3')
                    ->table('m_departments')
                    ->where('code', $codeDepartment)
                    ->value('name');

                // Menambahkan kolom section ke hasil data dari SQL Server
                $row->department = $department ? $department : 'Tidak Ada Data'; // Jika section tidak ada, beri nilai default
            }

            return $row;
        });

        // Pengecekan apakah data dengan empno yang sesuai dengan $loggedInNPK ada di $data
        $matchedData = $data->firstWhere('empno', $loggedInNPK);

        // Inisialisasi variabel $department dan $filteredData
        $department = null;
        $filteredData = collect(); // Membuat koleksi data kosong

        if ($matchedData) {
            $department = $matchedData->department;

            // Menyaring data berdasarkan $department yang sesuai
            $filteredData = $data->filter(function ($row) use ($department) {
                return $row->department === $department;
            });
        }

        // Mengelompokkan data berdasarkan empno
        $groupedData = $filteredData->groupBy('empno');

        return view('monthlyAttendanceDepartment', compact('groupedData'));
    }

    public function test()
    {
        $data = DB::connection('mysql3')
            ->table('m_divisions')
            ->select('*')
            ->get();

        dd($data);
    }
}
