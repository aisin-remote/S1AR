<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function getData(Request $request)
    {
        $tahunSekarang = Carbon::now()->year;
        $tanggalSekarang = Carbon::now()->format('Ymd');

        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT kehadiran2.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
            FROM kehadiran2
            LEFT JOIN hirarki ON kehadiran2.empno = hirarki.empno
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiran2.empno = $npk
            GROUP BY kehadiran2.empno, hirarki.hirar, hirarkidesc.descr
            ORDER BY mutdt DESC LIMIT 1;
            "
        ));

        if (!empty($userInfo)) {
            $npkDesc = $userInfo[0]->hirar; // Use array syntax

            $cleanedString = str_replace(' ', '', $npkDesc);

            // Hitung jumlah karakter
            $jumlahKarakter = strlen($cleanedString);

            // Tentukan jenis berdasarkan jumlah karakter
            if ($jumlahKarakter == 5) {
                $jenis = 'KDP';
            } elseif ($jumlahKarakter == 7) {
                $jenis = 'SPV';
            } elseif ($jumlahKarakter == 9) {
                $jenis = 'LDR/OPR';
            } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                $jenis = 'GMR';
            } else {
                $jenis = 'Jenis tidak dikenali'; // Atur jenis untuk kondisi lainnya
            }
        } else {
            // Handle the case where no results are returned
            $jenis = 'Jenis tidak dikenali';
        }

        $cleanedStringDept = str_replace(' ', '', $userInfo[0]->descr);
        $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDeptFinal;

        if ($request->input('start_date') != null && $request->input('end_date') != null) {
            $tanggalMulai = Carbon::parse($request->input('start_date'))->format('Ymd');
            $tanggalAkhir = Carbon::parse($request->input('end_date'))->format('Ymd');
        } elseif ($request->input('start_date') != null || $request->input('end_date') != null) {
            $tanggalMulai = $request->input('start_date') != null ? Carbon::parse($request->input('start_date'))->format('Ymd') : $tanggalSekarang;
            $tanggalAkhir = $request->input('end_date') != null ? Carbon::parse($request->input('end_date'))->format('Ymd') : $tanggalSekarang;
        } else {
            $tanggalMulai = $tanggalSekarang;
            $tanggalAkhir = $tanggalSekarang;
        }

        if ($userInfoOccupation == 'GMR' or $userInfoDept == 'HRD') {
            $data = DB::connection('mysql2')
                ->select(DB::raw("
            WITH CTE AS (
                SELECT 
                    kehadiran1.empno,
                    kehadiran1.datin,
                    kehadiran1.timin,
                    kehadiran1.datot,
                    kehadiran1.timot,
                    employee.empnm,
                    hirarki.hirar,
                    hirarkidesc.descr,
                    ROW_NUMBER() OVER (PARTITION BY kehadiran1.empno, kehadiran1.datin, kehadiran1.timin, kehadiran1.datot, kehadiran1.timot, employee.empnm ORDER BY hirarki.mutdt DESC) AS RowNum
                FROM kehadiran1 
                INNER JOIN employee ON kehadiran1.empno = employee.empno
                LEFT JOIN hirarki ON kehadiran1.empno = hirarki.empno
                LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                WHERE YEAR(kehadiran1.datin) = $tahunSekarang
                    AND kehadiran1.datin BETWEEN $tanggalMulai AND $tanggalAkhir
            )
            
            SELECT * 
            FROM CTE
            WHERE RowNum = 1
            ORDER BY empno ASC, datin ASC, timin ASC;
            "));
        } else if ($userInfoOccupation == 'KDP') {
            $data = DB::connection('mysql2')
                ->select(DB::raw('
            WITH CTE AS (
                SELECT 
                    kehadiran1.empno,
                    kehadiran1.datin,
                    kehadiran1.timin,
                    kehadiran1.datot,
                    kehadiran1.timot,
                    employee.empnm,
                    hirarki.hirar,
                    hirarkidesc.descr,
                    ROW_NUMBER() OVER (PARTITION BY kehadiran1.empno, kehadiran1.datin, kehadiran1.timin, kehadiran1.datot, kehadiran1.timot, employee.empnm ORDER BY hirarki.mutdt DESC) AS RowNum
                FROM kehadiran1 
                INNER JOIN employee ON kehadiran1.empno = employee.empno
                LEFT JOIN hirarki ON kehadiran1.empno = hirarki.empno
                LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                WHERE YEAR(kehadiran1.datin) = ' . $tahunSekarang . '
                    AND kehadiran1.datin BETWEEN ' . $tanggalMulai . ' AND ' . $tanggalAkhir . '
            )
            
            SELECT * 
            FROM CTE
            WHERE RowNum = 1
            AND descr LIKE \'%' . $userInfoDept . '%\'
            ORDER BY empno ASC, datin ASC, timin ASC;
            '));
        }

        $data = collect($data);

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

        // Iterate through each row in the collection
        foreach ($data as $row) {
            // Calculate the character count for each row's cleaned hirar
            $cleanedString = str_replace(' ', '', $row->hirar);
            $jumlahKarakter = strlen($cleanedString);

            // Determine jenis berdasarkan jumlah karakter
            if ($jumlahKarakter == 5) {
                $row->hirar = 'KDP';
            } elseif ($jumlahKarakter == 7) {
                $row->hirar = 'SPV';
            } elseif ($jumlahKarakter == 9) {
                $row->hirar = 'LDR/OPR';
            } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                $row->hirar = 'GMR';
            } else {
                $row->hirar = 'Jenis tidak dikenali'; // Atur jenis untuk kondisi lainnya
            }
        }

        return DataTables::of($data)->make(true);
    }

    public function getDataMonthly($month = null)
    {
        //set_time_limit(300); // Mengatur batas waktu eksekusi menjadi 5 menit
        ini_set('max_execution_time', 0);
        $tahunSekarang = Carbon::now()->year;
        $bulanSekarang = $month ?: Carbon::now()->month;

        $npk = auth()->user()->npk;

        $userInfo = DB::select(
            '
            SELECT TOP 1 attdly2.empno, pnhhira.hirar, MAX(pnhhira.mutdt) AS mutdt, ssmhira.descr
            FROM attdly2
            LEFT JOIN pnhhira ON attdly2.empno = pnhhira.empno
            LEFT JOIN ssmhira ON pnhhira.hirar = ssmhira.hirar
            WHERE attdly2.empno = ?
            GROUP BY attdly2.empno, pnhhira.hirar, ssmhira.descr
            ORDER BY mutdt DESC;
            ',
            [$npk]
        );

        if (!empty($userInfo)) {
            $npkDesc = $userInfo[0]->hirar; // Use array syntax

            $cleanedString = str_replace(' ', '', $npkDesc);

            // Hitung jumlah karakter
            $jumlahKarakter = strlen($cleanedString);

            // Tentukan jenis berdasarkan jumlah karakter
            if ($jumlahKarakter == 5) {
                $jenis = 'KDP';
            } elseif ($jumlahKarakter == 7) {
                $jenis = 'SPV';
            } elseif ($jumlahKarakter == 9) {
                $jenis = 'LDR/OPR';
            } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                $jenis = 'GMR';
            } else {
                $jenis = 'Jenis tidak dikenali'; // Atur jenis untuk kondisi lainnya
            }
        } else {
            // Handle the case where no results are returned
            $jenis = 'Jenis tidak dikenali';
        }

        $cleanedStringDept = str_replace(' ', '', $userInfo[0]->descr);
        $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDeptFinal;

        if ($userInfoOccupation == 'GMR' or $userInfoDept == 'HRD') {
            $data = DB::select('
        WITH CTE AS (
            SELECT 
                COALESCE(a.coid, b.coid) AS coid,
                COALESCE(a.empno, b.empno) AS empno,
                COALESCE(pnmempl_attdly2.empnm, pnmempl_atttrn2.empnm) AS empnm,
                COALESCE(a.schdt, b.schdt) AS schdt,
                COALESCE(a.rsccd, b.rsccd) AS rsccd,
                COALESCE(pnhhira_attdly2.hirar, pnhhira_atttrn2.hirar) AS hirar,
                COALESCE(pnhhira_attdly2.mutdt, pnhhira_atttrn2.mutdt) AS mutdt,
                COALESCE(ssmhira_attdly2.descr, ssmhira_atttrn2.descr) AS descr,
                ROW_NUMBER() OVER (PARTITION BY COALESCE(a.coid, b.coid), COALESCE(a.empno, b.empno), COALESCE(a.schdt, b.schdt) ORDER BY COALESCE(pnhhira_attdly2.mutdt, pnhhira_atttrn2.mutdt) DESC) AS RowNum
            FROM attdly2 a
            LEFT JOIN pnmempl pnmempl_attdly2 ON a.empno = pnmempl_attdly2.empno
            LEFT JOIN pnhhira pnhhira_attdly2 ON a.empno = pnhhira_attdly2.empno
            LEFT JOIN ssmhira ssmhira_attdly2 ON pnhhira_attdly2.hirar = ssmhira_attdly2.hirar
            FULL OUTER JOIN atttrn2 b ON a.coid = b.coid AND a.empno = b.empno AND a.schdt = b.schdt
            LEFT JOIN pnmempl pnmempl_atttrn2 ON b.empno = pnmempl_atttrn2.empno
            LEFT JOIN pnhhira pnhhira_atttrn2 ON b.empno = pnhhira_atttrn2.empno
            LEFT JOIN ssmhira ssmhira_atttrn2 ON pnhhira_atttrn2.hirar = ssmhira_atttrn2.hirar
            WHERE YEAR(COALESCE(a.schdt, b.schdt)) = ' . $tahunSekarang . ' AND MONTH(COALESCE(a.schdt, b.schdt)) = ' . $bulanSekarang . '
        )
        
        SELECT * 
        FROM CTE
        WHERE RowNum = 1 
        ORDER BY empno ASC, schdt ASC;
        ');
        } else if ($userInfoOccupation == 'KDP') {
            $data = DB::select('
        WITH CTE AS (
            SELECT 
                COALESCE(a.coid, b.coid) AS coid,
                COALESCE(a.empno, b.empno) AS empno,
                COALESCE(pnmempl_attdly2.empnm, pnmempl_atttrn2.empnm) AS empnm,
                COALESCE(a.schdt, b.schdt) AS schdt,
                COALESCE(a.rsccd, b.rsccd) AS rsccd,
                COALESCE(pnhhira_attdly2.hirar, pnhhira_atttrn2.hirar) AS hirar,
                COALESCE(pnhhira_attdly2.mutdt, pnhhira_atttrn2.mutdt) AS mutdt,
                COALESCE(ssmhira_attdly2.descr, ssmhira_atttrn2.descr) AS descr,
                ROW_NUMBER() OVER (PARTITION BY COALESCE(a.coid, b.coid), COALESCE(a.empno, b.empno), COALESCE(a.schdt, b.schdt) ORDER BY COALESCE(pnhhira_attdly2.mutdt, pnhhira_atttrn2.mutdt) DESC) AS RowNum
            FROM attdly2 a
            LEFT JOIN pnmempl pnmempl_attdly2 ON a.empno = pnmempl_attdly2.empno
            LEFT JOIN pnhhira pnhhira_attdly2 ON a.empno = pnhhira_attdly2.empno
            LEFT JOIN ssmhira ssmhira_attdly2 ON pnhhira_attdly2.hirar = ssmhira_attdly2.hirar
            FULL OUTER JOIN atttrn2 b ON a.coid = b.coid AND a.empno = b.empno AND a.schdt = b.schdt
            LEFT JOIN pnmempl pnmempl_atttrn2 ON b.empno = pnmempl_atttrn2.empno
            LEFT JOIN pnhhira pnhhira_atttrn2 ON b.empno = pnhhira_atttrn2.empno
            LEFT JOIN ssmhira ssmhira_atttrn2 ON pnhhira_atttrn2.hirar = ssmhira_atttrn2.hirar
            WHERE YEAR(COALESCE(a.schdt, b.schdt)) = ' . $tahunSekarang . ' AND MONTH(COALESCE(a.schdt, b.schdt)) = ' . $bulanSekarang . '
        )
        
        SELECT * 
        FROM CTE
        WHERE RowNum = 1
        AND descr LIKE \'%' . $userInfoDept . '%\'
        ORDER BY empno ASC, schdt ASC;
        ');
        } else {
            $data = DB::select('
        WITH CTE AS (
            SELECT 
                COALESCE(a.coid, b.coid) AS coid,
                COALESCE(a.empno, b.empno) AS empno,
                COALESCE(pnmempl_attdly2.empnm, pnmempl_atttrn2.empnm) AS empnm,
                COALESCE(a.schdt, b.schdt) AS schdt,
                COALESCE(a.rsccd, b.rsccd) AS rsccd,
                COALESCE(pnhhira_attdly2.hirar, pnhhira_atttrn2.hirar) AS hirar,
                COALESCE(pnhhira_attdly2.mutdt, pnhhira_atttrn2.mutdt) AS mutdt,
                COALESCE(ssmhira_attdly2.descr, ssmhira_atttrn2.descr) AS descr,
                ROW_NUMBER() OVER (PARTITION BY COALESCE(a.coid, b.coid), COALESCE(a.empno, b.empno), COALESCE(a.schdt, b.schdt) ORDER BY COALESCE(pnhhira_attdly2.mutdt, pnhhira_atttrn2.mutdt) DESC) AS RowNum
            FROM attdly2 a
            LEFT JOIN pnmempl pnmempl_attdly2 ON a.empno = pnmempl_attdly2.empno
            LEFT JOIN pnhhira pnhhira_attdly2 ON a.empno = pnhhira_attdly2.empno
            LEFT JOIN ssmhira ssmhira_attdly2 ON pnhhira_attdly2.hirar = ssmhira_attdly2.hirar
            FULL OUTER JOIN atttrn2 b ON a.coid = b.coid AND a.empno = b.empno AND a.schdt = b.schdt
            LEFT JOIN pnmempl pnmempl_atttrn2 ON b.empno = pnmempl_atttrn2.empno
            LEFT JOIN pnhhira pnhhira_atttrn2 ON b.empno = pnhhira_atttrn2.empno
            LEFT JOIN ssmhira ssmhira_atttrn2 ON pnhhira_atttrn2.hirar = ssmhira_atttrn2.hirar
            WHERE YEAR(COALESCE(a.schdt, b.schdt)) = ' . $tahunSekarang . ' AND MONTH(COALESCE(a.schdt, b.schdt)) = ' . $bulanSekarang . '
        )
        
        SELECT * 
        FROM CTE
        WHERE RowNum = 1
        AND empno = ' . $npk . '
        ORDER BY empno ASC, schdt ASC;
        ');
        }

        $data = collect($data);

        $groupedData = $data->groupBy('empno');


        return view('monthlyAttendance', compact('groupedData', 'bulanSekarang', 'userInfoOccupation', 'userInfoDept'));
    }

    public function indexPerson()
    {
        return view('HistoryPerson');
    }

    public function getDataPerson(Request $request)
    {
        // $tahunSekarang = Carbon::now()->year;
        $npk = auth()->user()->npk; // Ambil NPK dari user yang sedang login

        if ($request->input('start_date') != null) {
            $startDate = Carbon::parse($request->input('start_date'));
            $tahunSekarang = $startDate->year;
            $bulanSekarang = $startDate->month;
        } else {
            $tahunSekarang = Carbon::now()->year;
            $bulanSekarang = Carbon::now()->month;
        }

        $data = DB::connection('mysql2')
            ->select(DB::raw('
                SELECT kehadiran1.empno, kehadiran1.datin, kehadiran1.timin, kehadiran1.datot, kehadiran1.timot, employee.empnm
                FROM kehadiran1
                JOIN employee ON kehadiran1.empno = employee.empno
                WHERE YEAR(kehadiran1.datin) = ' . $tahunSekarang . '
                AND MONTH(kehadiran1.datin) = ' . $bulanSekarang . '
                AND kehadiran1.empno = ' . $npk . '
                ORDER BY kehadiran1.datin ASC, kehadiran1.timin ASC
            '));

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
}
