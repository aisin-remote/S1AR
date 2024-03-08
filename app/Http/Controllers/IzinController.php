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


class IzinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT kehadiranmu.empno, kehadiranmu.rsccd, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
            FROM kehadiranmu
            LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiranmu.empno = $npk
            GROUP BY kehadiranmu.empno, kehadiranmu.rsccd, hirarki.hirar, hirarkidesc.descr
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
        $data = collect($userInfo);

        $groupedData = $data->groupBy('rsccd');

        return view('izin', compact('groupedData','userInfoOccupation', 'userInfoDept'));
    }

    public function getData(Request $request)
    {
        $tanggalSekarang = Carbon::now()->format('Ymd');

        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT kehadiranmu.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
            FROM kehadiranmu
            LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiranmu.empno = $npk
            GROUP BY kehadiranmu.empno, hirarki.hirar, hirarkidesc.descr
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
                    schdt,
                    rsccd,
                    crtdt,
                    stts,
                    note,
                    empnm,
                    hirar,
                    mutdt,
                    descr
                FROM (
                    SELECT
                        kehadiranmu.empno,
                        kehadiranmu.schdt,
                        kehadiranmu.rsccd,
                        kehadiranmu.crtdt,
                        kehadiranmu.stts,
                        kehadiranmu.note,
                        employee.empnm,
                        hirarki.hirar,
                        hirarki.mutdt,
                        hirarkidesc.descr,
                        @row_number := CASE
                            WHEN kehadiranmu.empno != @empno_prev OR kehadiranmu.schdt != @schdt_prev OR kehadiranmu.crtdt != @crtdt_prev
                                THEN 1
                                ELSE @row_number + 1
                            END AS RowNum,
                        @empno_prev := kehadiranmu.empno,
                        @schdt_prev := kehadiranmu.schdt,
                        @crtdt_prev := kehadiranmu.crtdt
                    FROM kehadiranmu
                    INNER JOIN employee ON kehadiranmu.empno = employee.empno
                    LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE kehadiranmu.schdt BETWEEN $tanggalMulai AND $tanggalAkhir
                    AND kehadiranmu.rsccd IN ('SKT', 'IMU','DLU','IMP')
                ) AS numbered
                WHERE RowNum = 1
                ORDER BY empno ASC, schdt DESC, mutdt DESC;
            "));
        } else if ($userInfoOccupation == 'KDP') {
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @schdt_prev = NULL');

            // Execute main query
            $data = DB::connection('mysql2')
                ->select(DB::raw("
                SELECT
                    empno,
                    schdt,
                    rsccd,
                    crtdt,
                    stts,
                    note,
                    empnm,
                    hirar,
                    mutdt,
                    descr
                FROM (
                    SELECT
                        kehadiranmu.empno,
                        kehadiranmu.schdt,
                        kehadiranmu.rsccd,
                        kehadiranmu.crtdt,
                        kehadiranmu.stts,
                        kehadiranmu.note,
                        employee.empnm,
                        hirarki.hirar,
                        hirarki.mutdt,
                        hirarkidesc.descr,
                        @row_number := CASE
                            WHEN kehadiranmu.empno != @empno_prev OR kehadiranmu.schdt != @schdt_prev OR kehadiranmu.crtdt != @crtdt_prev
                                THEN 1
                                ELSE @row_number + 1
                            END AS RowNum,
                        @empno_prev := kehadiranmu.empno,
                        @schdt_prev := kehadiranmu.schdt,
                        @crtdt_prev := kehadiranmu.crtdt
                    FROM kehadiranmu
                    INNER JOIN employee ON kehadiranmu.empno = employee.empno
                    LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
                    LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
                    WHERE kehadiranmu.schdt BETWEEN $tanggalMulai AND $tanggalAkhir
                    AND kehadiranmu.rsccd IN ('CTH', 'CBS'')
                ) AS numbered
                WHERE RowNum = 1
                AND descr LIKE '%$userInfoDept%'
                ORDER BY empno ASC, schdt ASC, crtdt ASC, mutdt DESC;
            "));
        }

        // Initialize an associative array to store the latest mutdt for each empno and schdt
        $latestMutdt = [];

        // Filter the data based on the latest mutdt for each empno and schdt
        $filteredData = array_filter($data, function ($item) use (&$latestMutdt) {
            $key = $item->empno . $item->schdt;

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
            if ($row->schdt != "        ") {
                $row->schdt = substr($row->schdt, 0, 4) . '-' . substr($row->schdt, 4, 2) . '-' . substr($row->schdt, 6, 2);
                $row->crtdt = substr($row->crtdt, 0, 10);
            } else {
                $row->schdt = "Tidak Ada Data";
                $row->crtdt = "Tidak Ada Data";
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

    public function getDataCuzia($year = null, $month = null)
    {
        //set_time_limit(300); // Mengatur batas waktu eksekusi menjadi 5 menit
        ini_set('max_execution_time', 0);
        $dateArray = explode('-', $year);
        $tahunSekarang = $year ? $dateArray[0] : Carbon::now()->year;
        $bulanSekarang = $year ? $dateArray[1] : Carbon::now()->month;

        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT kehadiranmu.empno, kehadiranmu.rsccd, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
            FROM kehadiranmu
            LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiranmu.empno = $npk
            GROUP BY kehadiranmu.empno, kehadiranmu.rsccd, hirarki.hirar, hirarkidesc.descr
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

        if ($userInfoOccupation == 'GMR' or strpos($userInfoDept, 'HRD') or strpos($userInfoDept, 'KDP') === 0)  {
            $data = DB::connection('mysql2')->select('
            WITH MergedData AS (
                SELECT
                    k.coid,
                    k.empno,
                    k.schdt,
                    k.rsccd,
                    k.stts,
                    k.note,
                    e.empnm,
                    h.hirar,
                    hd.descr AS descr,
                    h.mutdt,
                    ROW_NUMBER() OVER (PARTITION BY k.empno, k.schdt ORDER BY h.mutdt DESC) AS RowNum
                FROM (

                    SELECT coid, empno, schdt, rsccd,stts,note FROM kehadiranmu
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
                md.stts,
                md.note,
                md.hirar,
                md.descr,
                md.mutdt
            FROM MergedData md
            WHERE md.RowNum = 1 AND YEAR(md.schdt) = ' . $tahunSekarang . ' AND MONTH(md.schdt) = ' . $bulanSekarang . ';
        ');
        // } else if ($userInfoOccupation == 'KDP') {
        //     $data = DB::connection('mysql2')->select('
        //     WITH MergedData AS (
        //         SELECT
        //             k.coid,
        //             k.empno,
        //             k.schdt,
        //             k.rsccd,
        //             k.stts,
        //             k.note,
        //             e.empnm,
        //             h.hirar,
        //             hd.descr AS descr,
        //             h.mutdt,
        //             ROW_NUMBER() OVER (PARTITION BY k.empno, k.schdt ORDER BY h.mutdt DESC) AS RowNum
        //         FROM (
        //             SELECT coid, empno, schdt, rsccd, stts, note FROM kehadiranmu
        //         ) AS k
        //         LEFT JOIN hirarki AS h ON k.empno = h.empno
        //         LEFT JOIN hirarkidesc AS hd ON h.hirar = hd.hirar
        //         LEFT JOIN employee AS e ON k.empno = e.empno
        //     )
        //     SELECT
        //         md.coid,
        //         md.empno,
        //         md.empnm,
        //         md.schdt,
        //         md.rsccd,
        //         md.stts,
        //         md.note,
        //         md.hirar,
        //         md.descr,
        //         md.mutdt
        //     FROM MergedData md
        //     WHERE md.RowNum = 3 AND YEAR(md.schdt) = ' . $tahunSekarang . ' AND MONTH(md.schdt) = ' . $bulanSekarang . ' AND md.descr LIKE \'%' . $userInfoDept . '%\';

        // ');
        } else {
            $data = DB::connection('mysql2')->select('
            WITH MergedData AS (
                SELECT
                    k.coid,
                    k.empno,
                    k.schdt,
                    k.rsccd,
                    k.stts,
                    k.note,
                    e.empnm,
                    h.hirar,
                    hd.descr AS descr,
                    h.mutdt,
                    ROW_NUMBER() OVER (PARTITION BY k.empno, k.schdt ORDER BY h.mutdt DESC) AS RowNum
                FROM (
                    SELECT coid, empno, schdt, rsccd, stts, note FROM kehadiranmu
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
                md.stts,
                md.note,
                md.hirar,
                md.descr,
                md.mutdt
            FROM MergedData md
            WHERE md.RowNum = 1 AND YEAR(md.schdt) = ' . $tahunSekarang . ' AND MONTH(md.schdt) = ' . $bulanSekarang . ';
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

        return view('cuzia1', compact('groupedData', 'holidays', 'bulanSekarang', 'tahunSekarang', 'userInfoOccupation', 'userInfoDept'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
