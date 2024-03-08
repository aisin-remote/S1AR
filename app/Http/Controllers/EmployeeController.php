<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Holiday;


class EmployeeController extends Controller
{
    public function index()
    {
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

        $cleanedStringDept = trim($userInfo[0]->descr);
        // $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;

        return view('index', compact('userInfoOccupation', 'userInfoDept'));
    }

    public function getData(Request $request)
    {
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

        $cleanedStringDept = trim($userInfo[0]->descr);
        // $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;

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

        if ($userInfoOccupation == 'GMR' or strpos($userInfoDept, 'HRD') === 0) {
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

            // Execute main query
            $data = DB::connection('mysql2')
                ->select(DB::raw("
                SELECT
                    empno,
                    datin,
                    timin,
                    datot,
                    timot,
                    empnm,
                    hirar,
                    mutdt,
                    descr
                FROM (
                    SELECT
                        kehadiran1.empno,
                        kehadiran1.datin,
                        kehadiran1.timin,
                        kehadiran1.datot,
                        kehadiran1.timot,
                        employee.empnm,
                        hirarki.hirar,
                        hirarki.mutdt,
                        hirarkidesc.descr,
                        @row_number := CASE
                            WHEN kehadiran1.empno != @empno_prev OR kehadiran1.datin != @datin_prev OR kehadiran1.timin != @timin_prev
                                THEN 1
                                ELSE @row_number + 1
                            END AS RowNum,
                        @empno_prev := kehadiran1.empno,
                        @datin_prev := kehadiran1.datin,
                        @timin_prev := kehadiran1.timin
                    FROM kehadiran1
                    INNER JOIN employee ON kehadiran1.empno = employee.empno
                    LEFT JOIN hirarki ON kehadiran1.empno = hirarki.empno
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE kehadiran1.datin BETWEEN $tanggalMulai AND $tanggalAkhir
                ) AS numbered
                WHERE RowNum = 1
                ORDER BY empno ASC, datin DESC, mutdt DESC;
            "));
        } else if ($userInfoOccupation == 'KDP') {
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

            // Execute main query
            $data = DB::connection('mysql2')
                ->select(DB::raw("
                SELECT
                    empno,
                    datin,
                    timin,
                    datot,
                    timot,
                    empnm,
                    hirar,
                    mutdt,
                    descr
                FROM (
                    SELECT
                        kehadiran1.empno,
                        kehadiran1.datin,
                        kehadiran1.timin,
                        kehadiran1.datot,
                        kehadiran1.timot,
                        employee.empnm,
                        hirarki.hirar,
                        hirarki.mutdt,
                        hirarkidesc.descr,
                        @row_number := CASE
                            WHEN kehadiran1.empno != @empno_prev OR kehadiran1.datin != @datin_prev OR kehadiran1.timin != @timin_prev
                                THEN 1
                                ELSE @row_number + 1
                            END AS RowNum,
                        @empno_prev := kehadiran1.empno,
                        @datin_prev := kehadiran1.datin,
                        @timin_prev := kehadiran1.timin
                    FROM kehadiran1
                    INNER JOIN employee ON kehadiran1.empno = employee.empno
                    LEFT JOIN hirarki ON kehadiran1.empno = hirarki.empno
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE kehadiran1.datin BETWEEN $tanggalMulai AND $tanggalAkhir
                ) AS numbered
                WHERE RowNum = 1
                AND descr LIKE '%$userInfoDept%'
                ORDER BY empno ASC, datin ASC, timin ASC, mutdt DESC;
            "));
        }

        // Initialize an associative array to store the latest mutdt for each empno and datin
        $latestMutdt = [];

        // Filter the data based on the latest mutdt for each empno and datin
        $filteredData = array_filter($data, function ($item) use (&$latestMutdt) {
            $key = $item->empno . $item->datin;

            // Check if the key already exists in $latestMutdt
            if (!isset($latestMutdt[$key]) || $item->mutdt > $latestMutdt[$key]->mutdt) {
                // Update the latest mutdt for this key
                $latestMutdt[$key] = $item;
                return true;
            }

            return false;
        });

        // Reindex the array to reset keys
        $filteredData = array_values($filteredData);

        $data = collect($filteredData);

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

    public function getDataMonthly($year = null, $month = null)
    {
        //set_time_limit(300); // Mengatur batas waktu eksekusi menjadi 5 menit
        ini_set('max_execution_time', 0);
        $dateArray = explode('-', $year);
        $tahunSekarang = $year ? $dateArray[0] : Carbon::now()->year;
        $bulanSekarang = $year ? $dateArray[1] : Carbon::now()->month;

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

        $cleanedStringDept = trim($userInfo[0]->descr);
        // $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;

        // dd(strpos($userInfoDept, 'HRD') === 0);

        // dd($userInfoOccupation);

        if ($userInfoOccupation == 'GMR' or strpos($userInfoDept, 'HRD') === 0) {
            $data = DB::connection('mysql2')->select('
            WITH MergedData AS (
                SELECT
                    k.coid,
                    k.empno,
                    k.schdt,
                    k.rsccd,
                    e.empnm,
                    h.hirar,
                    hd.descr AS descr,
                    h.mutdt,
                    ROW_NUMBER() OVER (PARTITION BY k.empno, k.schdt ORDER BY h.mutdt DESC) AS RowNum
                FROM (
                    SELECT coid, empno, schdt, rsccd FROM kehadiran2
                    UNION ALL
                    SELECT coid, empno, schdt, rsccd FROM kehadiranmu
                ) AS k
                LEFT JOIN hirarki AS h ON k.empno = h.empno
                LEFT JOIN hirarkidesc AS hd ON h.hirar = hd.hirar
                LEFT JOIN employee AS e ON k.empno = e.empno
            )
            SELECT
                md.coid,
                md.empno,
                md.empnm,
                md.schdt,
                md.rsccd,
                md.hirar,
                md.descr,
                md.mutdt
            FROM MergedData md
            WHERE md.RowNum = 1 AND YEAR(md.schdt) = ' . $tahunSekarang . ' AND MONTH(md.schdt) = ' . $bulanSekarang . ';
        ');
        } else if ($userInfoOccupation == 'KDP') {
            $data = DB::connection('mysql2')->select('
            WITH MergedData AS (
                SELECT
                    k.coid,
                    k.empno,
                    k.schdt,
                    k.rsccd,
                    e.empnm,
                    h.hirar,
                    hd.descr AS descr,
                    h.mutdt,
                    ROW_NUMBER() OVER (PARTITION BY k.empno, k.schdt ORDER BY h.mutdt DESC) AS RowNum
                FROM (
                    SELECT coid, empno, schdt, rsccd FROM kehadiran2
                    UNION ALL
                    SELECT coid, empno, schdt, rsccd FROM kehadiranmu
                ) AS k
                LEFT JOIN hirarki AS h ON k.empno = h.empno
                LEFT JOIN hirarkidesc AS hd ON h.hirar = hd.hirar
                LEFT JOIN employee AS e ON k.empno = e.empno
            )
            SELECT
                md.coid,
                md.empno,
                md.empnm,
                md.schdt,
                md.rsccd,
                md.hirar,
                md.descr,
                md.mutdt
            FROM MergedData md
            WHERE md.RowNum = 1 AND YEAR(md.schdt) = ' . $tahunSekarang . ' AND MONTH(md.schdt) = ' . $bulanSekarang . ' AND md.descr LIKE \'%' . $userInfoDept . '%\';
        ');
        } else {
            $data = DB::connection('mysql2')->select('
            WITH MergedData AS (
                SELECT
                    k.coid,
                    k.empno,
                    k.schdt,
                    k.rsccd,
                    e.empnm,
                    h.hirar,
                    hd.descr AS descr,
                    h.mutdt,
                    ROW_NUMBER() OVER (PARTITION BY k.empno, k.schdt ORDER BY h.mutdt DESC) AS RowNum
                FROM (
                    SELECT coid, empno, schdt, rsccd FROM kehadiran2
                    UNION ALL
                    SELECT coid, empno, schdt, rsccd FROM kehadiranmu
                ) AS k
                LEFT JOIN hirarki AS h ON k.empno = h.empno
                LEFT JOIN hirarkidesc AS hd ON h.hirar = hd.hirar
                LEFT JOIN employee AS e ON k.empno = e.empno
            )
            SELECT
                md.coid,
                md.empno,
                md.empnm,
                md.schdt,
                md.rsccd,
                md.hirar,
                md.descr,
                md.mutdt
            FROM MergedData md
            WHERE md.RowNum = 1 AND YEAR(md.schdt) = ' . $tahunSekarang . ' AND MONTH(md.schdt) = ' . $bulanSekarang . ' AND md.empno = ' . $npk . ' ;
        ');
        }

        // $cacheKey = 'monthly_data_' . $npk . '_' . $tahunSekarang . '_' . $bulanSekarang;
        // if (Cache::has($cacheKey)) {
        //     $groupedData = Cache::get($cacheKey);
        // } else {
        //     if ($userInfoOccupation == 'GMR' or $userInfoDept == 'HRD') {
        //         // Set variables
        //         DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

        //         // Execute main query
        //         $data = DB::connection('mysql2')
        //             ->select(DB::raw('
        //             SELECT
        //                 coid,
        //                 empno,
        //                 empnm,
        //                 schdt,
        //                 rsccd,
        //                 hirar,
        //                 mutdt,
        //                 descr
        //             FROM (
        //                 SELECT
        //                     COALESCE(a.coid, b.coid) AS coid,
        //                     COALESCE(a.empno, b.empno) AS empno,
        //                     COALESCE(employee_kehadiran2.empnm, employee_kehadiranmu.empnm) AS empnm,
        //                     COALESCE(a.schdt, b.schdt) AS schdt,
        //                     COALESCE(a.rsccd, b.rsccd) AS rsccd,
        //                     COALESCE(hirarki_kehadiran2.hirar, hirarki_kehadiranmu.hirar) AS hirar,
        //                     COALESCE(hirarki_kehadiran2.mutdt, hirarki_kehadiranmu.mutdt) AS mutdt,
        //                     COALESCE(hirarkidesc_kehadiran2.descr, hirarkidesc_kehadiranmu.descr) AS descr,
        //                     @row_number := CASE
        //                         WHEN COALESCE(a.empno, b.empno) != @empno_prev OR COALESCE(a.schdt, b.schdt) != @schdt_prev
        //                             THEN 1
        //                             ELSE @row_number + 1
        //                         END AS RowNum,
        //                     @empno_prev := COALESCE(a.empno, b.empno),
        //                     @schdt_prev := COALESCE(a.schdt, b.schdt)
        //                 FROM kehadiran2 a
        //                 LEFT JOIN employee employee_kehadiran2 ON a.empno = employee_kehadiran2.empno
        //                 LEFT JOIN hirarki hirarki_kehadiran2 ON a.empno = hirarki_kehadiran2.empno
        //                 LEFT JOIN hirarkidesc hirarkidesc_kehadiran2 ON hirarki_kehadiran2.hirar = hirarkidesc_kehadiran2.hirar
        //                 LEFT JOIN kehadiranmu b ON a.coid = b.coid AND a.empno = b.empno AND a.schdt = b.schdt
        //                 LEFT JOIN employee employee_kehadiranmu ON b.empno = employee_kehadiranmu.empno
        //                 LEFT JOIN hirarki hirarki_kehadiranmu ON b.empno = hirarki_kehadiranmu.empno
        //                 LEFT JOIN hirarkidesc hirarkidesc_kehadiranmu ON hirarki_kehadiranmu.hirar = hirarkidesc_kehadiranmu.hirar
        //                 WHERE YEAR(COALESCE(a.schdt, b.schdt)) = ' . $tahunSekarang . ' AND MONTH(COALESCE(a.schdt, b.schdt)) = ' . $bulanSekarang . '
        //             ) AS numbered
        //             WHERE RowNum = 1
        //             ORDER BY empno ASC, schdt ASC, mutdt DESC
        //         '));
        //     } else if ($userInfoOccupation == 'KDP') {
        //         // Set variables
        //         DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

        //         // Execute main query
        //         $data = DB::connection('mysql2')
        //             ->select(DB::raw('
        //             SELECT
        //                 coid,
        //                 empno,
        //                 empnm,
        //                 schdt,
        //                 rsccd,
        //                 hirar,
        //                 mutdt,
        //                 descr
        //             FROM (
        //                 SELECT
        //                     COALESCE(a.coid, b.coid) AS coid,
        //                     COALESCE(a.empno, b.empno) AS empno,
        //                     COALESCE(employee_kehadiran2.empnm, employee_kehadiranmu.empnm) AS empnm,
        //                     COALESCE(a.schdt, b.schdt) AS schdt,
        //                     COALESCE(a.rsccd, b.rsccd) AS rsccd,
        //                     COALESCE(hirarki_kehadiran2.hirar, hirarki_kehadiranmu.hirar) AS hirar,
        //                     COALESCE(hirarki_kehadiran2.mutdt, hirarki_kehadiranmu.mutdt) AS mutdt,
        //                     COALESCE(hirarkidesc_kehadiran2.descr, hirarkidesc_kehadiranmu.descr) AS descr,
        //                     @row_number := CASE
        //                         WHEN COALESCE(a.empno, b.empno) != @empno_prev OR COALESCE(a.schdt, b.schdt) != @schdt_prev
        //                             THEN 1
        //                             ELSE @row_number + 1
        //                         END AS RowNum,
        //                     @empno_prev := COALESCE(a.empno, b.empno),
        //                     @schdt_prev := COALESCE(a.schdt, b.schdt)
        //                 FROM kehadiran2 a
        //                 LEFT JOIN employee employee_kehadiran2 ON a.empno = employee_kehadiran2.empno
        //                 LEFT JOIN hirarki hirarki_kehadiran2 ON a.empno = hirarki_kehadiran2.empno
        //                 LEFT JOIN hirarkidesc hirarkidesc_kehadiran2 ON hirarki_kehadiran2.hirar = hirarkidesc_kehadiran2.hirar
        //                 LEFT JOIN kehadiranmu b ON a.coid = b.coid AND a.empno = b.empno AND a.schdt = b.schdt
        //                 LEFT JOIN employee employee_kehadiranmu ON b.empno = employee_kehadiranmu.empno
        //                 LEFT JOIN hirarki hirarki_kehadiranmu ON b.empno = hirarki_kehadiranmu.empno
        //                 LEFT JOIN hirarkidesc hirarkidesc_kehadiranmu ON hirarki_kehadiranmu.hirar = hirarkidesc_kehadiranmu.hirar
        //                 WHERE YEAR(COALESCE(a.schdt, b.schdt)) = ' . $tahunSekarang . ' AND MONTH(COALESCE(a.schdt, b.schdt)) = ' . $bulanSekarang . '
        //             ) AS numbered
        //             WHERE RowNum = 1
        //             AND descr LIKE \'%' . $userInfoDept . '%\'
        //             ORDER BY empno ASC, schdt ASC, mutdt DESC
        //         '));
        //     } else {
        //         // Set variables
        //         DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

        //         // Execute main query
        //         $data = DB::connection('mysql2')
        //             ->select(DB::raw('
        //             SELECT
        //                 coid,
        //                 empno,
        //                 empnm,
        //                 schdt,
        //                 rsccd,
        //                 hirar,
        //                 mutdt,
        //                 descr
        //             FROM (
        //                 SELECT
        //                     COALESCE(a.coid, b.coid) AS coid,
        //                     COALESCE(a.empno, b.empno) AS empno,
        //                     COALESCE(employee_kehadiran2.empnm, employee_kehadiranmu.empnm) AS empnm,
        //                     COALESCE(a.schdt, b.schdt) AS schdt,
        //                     COALESCE(a.rsccd, b.rsccd) AS rsccd,
        //                     COALESCE(hirarki_kehadiran2.hirar, hirarki_kehadiranmu.hirar) AS hirar,
        //                     COALESCE(hirarki_kehadiran2.mutdt, hirarki_kehadiranmu.mutdt) AS mutdt,
        //                     COALESCE(hirarkidesc_kehadiran2.descr, hirarkidesc_kehadiranmu.descr) AS descr,
        //                     @row_number := CASE
        //                         WHEN COALESCE(a.empno, b.empno) != @empno_prev OR COALESCE(a.schdt, b.schdt) != @schdt_prev
        //                             THEN 1
        //                             ELSE @row_number + 1
        //                         END AS RowNum,
        //                     @empno_prev := COALESCE(a.empno, b.empno),
        //                     @schdt_prev := COALESCE(a.schdt, b.schdt)
        //                 FROM kehadiran2 a
        //                 LEFT JOIN employee employee_kehadiran2 ON a.empno = employee_kehadiran2.empno
        //                 LEFT JOIN hirarki hirarki_kehadiran2 ON a.empno = hirarki_kehadiran2.empno
        //                 LEFT JOIN hirarkidesc hirarkidesc_kehadiran2 ON hirarki_kehadiran2.hirar = hirarkidesc_kehadiran2.hirar
        //                 LEFT JOIN kehadiranmu b ON a.coid = b.coid AND a.empno = b.empno AND a.schdt = b.schdt
        //                 LEFT JOIN employee employee_kehadiranmu ON b.empno = employee_kehadiranmu.empno
        //                 LEFT JOIN hirarki hirarki_kehadiranmu ON b.empno = hirarki_kehadiranmu.empno
        //                 LEFT JOIN hirarkidesc hirarkidesc_kehadiranmu ON hirarki_kehadiranmu.hirar = hirarkidesc_kehadiranmu.hirar
        //                 WHERE YEAR(COALESCE(a.schdt, b.schdt)) = ' . $tahunSekarang . ' AND MONTH(COALESCE(a.schdt, b.schdt)) = ' . $bulanSekarang . '
        //             ) AS numbered
        //             WHERE RowNum = 1
        //             AND empno = ' . $npk . '
        //             ORDER BY empno ASC, schdt ASC, mutdt DESC
        //         '));
        //     }

        //     $data = collect($data);

        //     $groupedData = $data->groupBy('empno');

        //     // Simpan data ke cache dengan waktu kadaluarsa 1 jam (3600 detik)
        //     Cache::put($cacheKey, $groupedData, 7200);
        // }

        $data = collect($data);

        $groupedData = $data->groupBy('empno');

        $holidays = Holiday::whereYear('date', $tahunSekarang)
            ->whereMonth('date', $bulanSekarang)
            ->get();

        return view('monthlyAttendance', compact('groupedData', 'holidays', 'bulanSekarang', 'tahunSekarang', 'userInfoOccupation', 'userInfoDept'));
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

        DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

        $data = DB::connection('mysql2')
            ->select(DB::raw("
                SELECT
                    empno,
                    datin,
                    timin,
                    datot,
                    timot,
                    empnm,
                    hirar,
                    mutdt,
                    descr
                FROM (
                    SELECT
                        kehadiran1.empno,
                        kehadiran1.datin,
                        kehadiran1.timin,
                        kehadiran1.datot,
                        kehadiran1.timot,
                        employee.empnm,
                        hirarki.hirar,
                        hirarki.mutdt,
                        hirarkidesc.descr,
                        @row_number := CASE
                            WHEN kehadiran1.empno != @empno_prev OR kehadiran1.datin != @datin_prev OR kehadiran1.timin != @timin_prev
                                THEN 1
                                ELSE @row_number + 1
                            END AS RowNum,
                        @empno_prev := kehadiran1.empno,
                        @datin_prev := kehadiran1.datin,
                        @timin_prev := kehadiran1.timin
                    FROM kehadiran1
                    INNER JOIN employee ON kehadiran1.empno = employee.empno
                    LEFT JOIN hirarki ON kehadiran1.empno = hirarki.empno
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE YEAR(kehadiran1.datin) = $tahunSekarang
                        AND MONTH(kehadiran1.datin) = $bulanSekarang
                ) AS numbered
                WHERE RowNum = 1
                AND empno = $npk
                ORDER BY empno ASC, datin DESC, mutdt DESC;
            "));

        // Initialize an associative array to store the latest mutdt for each empno and datin
        $latestMutdt = [];

        // Filter the data based on the latest mutdt for each empno and datin
        $filteredData = array_filter($data, function ($item) use (&$latestMutdt) {
            $key = $item->empno . $item->datin;

            // Check if the key already exists in $latestMutdt
            if (!isset($latestMutdt[$key]) || $item->mutdt > $latestMutdt[$key]->mutdt) {
                // Update the latest mutdt for this key
                $latestMutdt[$key] = $item;
                return true;
            }

            return false;
        });

        // Reindex the array to reset keys
        $filteredData = array_values($filteredData);

        $data = collect($filteredData);

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
}
