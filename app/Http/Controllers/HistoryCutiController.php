<?php

namespace App\Http\Controllers;

use App\Models\jenisizin;
use Carbon\Carbon;
use App\Models\saldoCuti;
use Illuminate\Http\Request;
use Termwind\Components\Raw;
use App\Models\PengajuanCuti;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class HistoryCutiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
        // dd(trim($userInfo[0]->hirar));
        // $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;
        $data = collect($userInfo);
        $jenisizin = jenisizin::select('id', 'jenisizin')
            ->where('jenisizin', 'LIKE', '%Cuti%')
            ->get();

        // dd($userInfoOccupation);
        return view('historycuti', compact('userInfoOccupation', 'userInfoDept', 'jenisizin'));
        // dd($request->all());
    }

    public function getData(Request $request)
    {
        $tanggalSekarang = Carbon::now()->format('Ymd');
        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(
            DB::raw(
                "
            SELECT kehadiran2.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr, users.is_admin
            FROM kehadiran2
            LEFT JOIN hirarki ON kehadiran2.empno = hirarki.empno
            LEFT JOIN users ON kehadiran2.empno = users.npk
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiran2.empno = :npk
            GROUP BY kehadiran2.empno, hirarki.hirar, hirarkidesc.descr, users.is_admin
            ORDER BY mutdt DESC LIMIT 1;
            "
            ),
            ['npk' => $npk]
        );

        if (!empty($userInfo)) {
            $npkDesc = $userInfo[0]->hirar;

            $cleanedString = str_replace(' ', '', $npkDesc);
            $jumlahKarakter = strlen($cleanedString);
            $isadmin = $userInfo[0]->is_admin;

            // Determine the role based on the character count
            $roles = ['Karyawan'];
            if ($isadmin == 1) {
                $roles[] = 'HRD Admin';
            }

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
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Handle dates
        if (!empty($start_date) && !empty($end_date)) {
            $tanggalMulai = Carbon::parse($start_date)->format('Y-m-d');
            $tanggalAkhir = Carbon::parse($end_date)->format('Y-m-d');
        } else {
            $tanggalMulai = null;
            $tanggalAkhir = null;
        }
        if ($userInfo[0]->is_admin == 1) {
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_pengajuan_prev = NULL');
            $baseQuery = "
                SELECT
                    id,
                    empno,
                    tgl_mulai,
                    tgl_selesai,
                    jeniscuti,
                    tgl_pengajuan,
                    approval1_status,
                    approval1_id,
                    approval2_id,
                    approval_status,
                    jenisizin,
                    note,
                    empnm,
                    hirar
                FROM (
                    SELECT
                        pc.id,
                        pc.empno,
                        pc.tgl_mulai,
                        pc.tgl_selesai,
                        pc.jeniscuti,
                        pc.tgl_pengajuan,
                        pc.approval1_status,
                        pc.approval1_id,
                        pc.approval2_id,
                        pc.approval_status,
                        pc.note,
                        jz.jenisizin,
                        e.empnm,
                        h.hirar,
                        ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                    FROM pengajuancuti pc
                    INNER JOIN employee e ON pc.empno = e.empno
                    INNER JOIN jenisizin jz ON pc.jeniscuti = jz.id
                    INNER JOIN (
                        SELECT empno, MAX(mutdt) AS max_mutdt
                        FROM hirarki
                        GROUP BY empno
                    ) max_hirarki ON pc.empno = max_hirarki.empno
                    INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                    WHERE 1=1"; // Start with a true condition to simplify query building

            // Add date conditions if provided
            if ($tanggalMulai && $tanggalAkhir) {
                $baseQuery .= " AND STR_TO_DATE(pc.tgl_mulai, '%Y-%m-%d') BETWEEN :tanggalMulai AND :tanggalAkhir";
            }

            $baseQuery .= "
                ) AS numbered
                WHERE RowNum = 1
                ORDER BY empno ASC, tgl_mulai DESC;
            ";

            // Bind parameters for date conditions
            $params = [];
            if ($tanggalMulai && $tanggalAkhir) {
                $params['tanggalMulai'] = $tanggalMulai;
                $params['tanggalAkhir'] = $tanggalAkhir;
            }

            try {
                // Execute query
                $data = DB::connection('mysql2')->select(DB::raw($baseQuery), $params);
            } catch (\Exception $e) {
                // Handle any exceptions here
                // Example: Log or return an error message
                dd($e->getMessage()); // Temporary debugging, replace with appropriate error handling
            }
        } elseif ($userInfoOccupation == 'KDP') {
            set_time_limit(600);
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_pengajuan_prev = NULL');
            $baseQuery = "
                SELECT
                    id,
                    empno,
                    tgl_mulai,
                    tgl_selesai,
                    jeniscuti,
                    tgl_pengajuan,
                    approval1_status,
                    approval1_id,
                    approval2_id,
                    approval_status,
                    jenisizin,
                    note,
                    empnm,
                    hirar,
                    mutdt,
                    descr,
                    is_admin
                FROM (
                    SELECT
                        pc.id,
                        pc.empno,
                        pc.tgl_mulai,
                        pc.tgl_selesai,
                        pc.jeniscuti,
                        pc.tgl_pengajuan,
                        pc.approval1_status,
                        pc.approval1_id,
                        pc.approval2_id,
                        pc.approval_status,
                        pc.note,
                        jz.jenisizin,
                        u.is_admin,
                        e.empnm,
                        h.hirar,
                        h.mutdt,
                        hd.descr,
                        ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                    FROM pengajuancuti pc
                    INNER JOIN employee e ON pc.empno = e.empno
                    INNER JOIN jenisizin jz ON pc.jeniscuti = jz.id
                    INNER JOIN users u ON pc.empno = u.npk
                    INNER JOIN (
                        SELECT empno, MAX(mutdt) AS max_mutdt
                        FROM hirarki
                        GROUP BY empno
                    ) max_hirarki ON pc.empno = max_hirarki.empno
                    INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                    INNER JOIN hirarkidesc hd ON h.hirar = hd.hirar
                    WHERE pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NOT NULL
                ";

            // Add date conditions if provided
            if ($tanggalMulai && $tanggalAkhir) {
                $baseQuery .= " AND STR_TO_DATE(pc.tgl_mulai, '%Y-%m-%d') BETWEEN :tanggalMulai AND :tanggalAkhir";
            }

            $baseQuery .= "
                ) AS numbered
                WHERE RowNum = 1
                ORDER BY empno ASC, tgl_mulai DESC;
            ";

            // Prepare parameters for binding
            $params = [];
            if ($tanggalMulai && $tanggalAkhir) {
                $params['tanggalMulai'] = $tanggalMulai;
                $params['tanggalAkhir'] = $tanggalAkhir;
            }

            // Execute query
            $data = DB::connection('mysql2')->select(DB::raw($baseQuery), $params);
        } elseif ($userInfoOccupation == 'SPV') {
            set_time_limit(600);
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_pengajuan_prev = NULL');
            $baseQuery = "
                SELECT
                    id,
                    empno,
                    tgl_mulai,
                    tgl_selesai,
                    jeniscuti,
                    tgl_pengajuan,
                    approval1_status,
                    approval1_id,
                    approval2_id,
                    approval_status,
                    jenisizin,
                    note,
                    empnm,
                    hirar,
                    mutdt,
                    descr,
                    is_admin
                FROM (
                    SELECT
                        pc.id,
                        pc.empno,
                        pc.tgl_mulai,
                        pc.tgl_selesai,
                        pc.jeniscuti,
                        pc.tgl_pengajuan,
                        pc.approval1_status,
                        pc.approval1_id,
                        pc.approval2_id,
                        pc.approval_status,
                        pc.note,
                        jz.jenisizin,
                        u.is_admin,
                        e.empnm,
                        h.hirar,
                        h.mutdt,
                        hd.descr,
                        ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                    FROM pengajuancuti pc
                    INNER JOIN employee e ON pc.empno = e.empno
                    INNER JOIN jenisizin jz ON pc.jeniscuti = jz.id
                    INNER JOIN users u ON pc.empno = u.npk
                    INNER JOIN (
                        SELECT empno, MAX(mutdt) AS max_mutdt
                        FROM hirarki
                        GROUP BY empno
                    ) max_hirarki ON pc.empno = max_hirarki.empno
                    INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                    INNER JOIN hirarkidesc hd ON h.hirar = hd.hirar
                    WHERE pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NOT NULL
                ";

            // Add date conditions if provided
            if ($tanggalMulai && $tanggalAkhir) {
                $baseQuery .= " AND STR_TO_DATE(pc.tgl_mulai, '%Y-%m-%d') BETWEEN :tanggalMulai AND :tanggalAkhir";
            }

            $baseQuery .= "
                ) AS numbered
                WHERE RowNum = 1
                ORDER BY empno ASC, tgl_mulai DESC;
            ";

            // Prepare parameters for binding
            $params = [];
            if ($tanggalMulai && $tanggalAkhir) {
                $params['tanggalMulai'] = $tanggalMulai;
                $params['tanggalAkhir'] = $tanggalAkhir;
            }

            // Execute query
            $data = DB::connection('mysql2')->select(DB::raw($baseQuery), $params);
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

        // Return DataTables response
        return DataTables::of($data)->make(true);
    }

    public function saldoCuti(Request $request)
    {
        set_time_limit(600); // Increase time limit
        // Your existing code to get the NPK of the logged-in user.
        $npk = $request->user()->npk;

        // Your existing code to retrieve the saldo cuti data.
        $result = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT
            (SELECT CONVERT((clrig - clget), CHAR) AS saldocutiistimewa
            FROM pengajuancutikar
            WHERE pengajuancutikar.empno = $npk
            ORDER BY expdt DESC
            LIMIT 1) AS saldocutiistimewa,
            (SELECT CONVERT((clrig - clget), CHAR) AS saldocutiistimewa
            FROM pengajuancutikar
            WHERE pengajuancutikar.empno = $npk
            ORDER BY expdt DESC
            LIMIT 1 OFFSET 2) AS saldocutitahunan;
            "
        ));

        // Check if the result is not empty and get the first element of the array.
        $saldocutiistimewa = !empty($result) ? (string) $result[0]->saldocutiistimewa : '0';
        // Check if the result is not empty and get the first element of the array.
        $saldocutitahunan = !empty($result) ? (string) $result[0]->saldocutitahunan : '0';

        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT
            kehadiranmu.empno,
            hirarki.hirar,
            MAX(hirarki.mutdt) AS mutdt,
            hirarkidesc.descr,
            users.is_admin,
            pc.approval1_id,
            pc.approval2_id
            FROM kehadiranmu
            LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
            LEFT JOIN users ON kehadiranmu.empno = users.npk
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            LEFT JOIN pengajuancuti pc ON kehadiranmu.empno = pc.empno
            WHERE kehadiranmu.empno = $npk
            GROUP BY
            kehadiranmu.empno,
            hirarki.hirar,
            hirarkidesc.descr,
            users.is_admin,
            pc.approval1_id,
            pc.approval2_id
            ORDER BY mutdt DESC
            LIMIT 1;
            "
        ));
        DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_pengajuan_prev = NULL');
        $result1 = DB::connection('mysql2')->select(DB::raw(
            "
           SELECT final_query.jumlahpengajuancuti1, final_query.empnm AS nama_karyawan
        FROM (
            SELECT
                COUNT(*) OVER () AS jumlahpengajuancuti1,
                id,
                empno,
                tgl_mulai,
                tgl_selesai,
                jeniscuti,
                tgl_pengajuan,
                approval1_status,
                approval1_id,
                approval2_id,
                approval_status,
                jenisizin,
                note,
                empnm,
                hirar
            FROM (
                SELECT
                    pc.id,
                    pc.empno,
                    pc.tgl_mulai,
                    pc.tgl_selesai,
                    pc.jeniscuti,
                    pc.tgl_pengajuan,
                    pc.approval1_status,
                    pc.approval1_id,
                    pc.approval2_id,
                    pc.approval_status,
                    pc.note,
                    jz.jenisizin,
                    e.empnm,
                    h.hirar,
                    ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                FROM pengajuancuti pc
                INNER JOIN employee e ON pc.empno = e.empno
                INNER JOIN jenisizin jz ON pc.jeniscuti = jz.id
                INNER JOIN (
                    SELECT empno, MAX(mutdt) AS max_mutdt
                    FROM hirarki
                    GROUP BY empno
                ) max_hirarki ON pc.empno = max_hirarki.empno
                INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                WHERE pc.approval_status IN (0, 1, 2)
            ) AS numbered
            WHERE RowNum = 1
        ) AS final_query;

            "
        ));
        $jumlahpengajuancuti1 = !empty($result1) ? (string) $result1[0]->jumlahpengajuancuti1 : '0';

        $nama_karyawan = !empty($result1) ? implode(', ', array_column($result1, 'nama_karyawan')) : '0';
        //  $pengajuanCutiList = !empty($result7) ? implode(', ', array_column($result7, 'nama_karyawan')) : '0';

        $result2 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT final_query.jumlahpengajuancuti2, final_query.empnm AS nama_karyawan
        FROM (
            SELECT
                COUNT(*) OVER () AS jumlahpengajuancuti2,
                id,
                empno,
                tgl_mulai,
                tgl_selesai,
                jeniscuti,
                tgl_pengajuan,
                approval1_status,
                approval1_id,
                approval2_id,
                approval_status,
                jenisizin,
                note,
                empnm,
                hirar
            FROM (
                SELECT
                    pc.id,
                    pc.empno,
                    pc.tgl_mulai,
                    pc.tgl_selesai,
                    pc.jeniscuti,
                    pc.tgl_pengajuan,
                    pc.approval1_status,
                    pc.approval1_id,
                    pc.approval2_id,
                    pc.approval_status,
                    pc.note,
                    jz.jenisizin,
                    e.empnm,
                    h.hirar,
                    ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                FROM pengajuancuti pc
                INNER JOIN employee e ON pc.empno = e.empno
                INNER JOIN jenisizin jz ON pc.jeniscuti = jz.id
                INNER JOIN (
                    SELECT empno, MAX(mutdt) AS max_mutdt
                    FROM hirarki
                    GROUP BY empno
                ) max_hirarki ON pc.empno = max_hirarki.empno
                INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                 WHERE  approval_status  LIKE '%0%' AND pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NULL
            ) AS numbered
            WHERE RowNum = 1
        ) AS final_query;
            "
        ));
        $jumlahpengajuancuti2 = !empty($result2) ? (string) $result2[0]->jumlahpengajuancuti2 : '0';
        $pengajuanCutiList = !empty($result2) ? implode(', ', array_column($result2, 'nama_karyawan')) : '0';
        $result7 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT final_query.jumlahpengajuancuti3, final_query.empnm AS nama_karyawan4
        FROM (
            SELECT
                COUNT(*) OVER () AS jumlahpengajuancuti3,
                id,
                empno,
                tgl_mulai,
                tgl_selesai,
                jeniscuti,
                tgl_pengajuan,
                approval1_status,
                approval1_id,
                approval2_id,
                approval_status,
                jenisizin,
                note,
                empnm,
                hirar
            FROM (
                SELECT
                    pc.id,
                    pc.empno,
                    pc.tgl_mulai,
                    pc.tgl_selesai,
                    pc.jeniscuti,
                    pc.tgl_pengajuan,
                    pc.approval1_status,
                    pc.approval1_id,
                    pc.approval2_id,
                    pc.approval_status,
                    pc.note,
                    jz.jenisizin,
                    e.empnm,
                    h.hirar,
                    ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                FROM pengajuancuti pc
                INNER JOIN employee e ON pc.empno = e.empno
                INNER JOIN jenisizin jz ON pc.jeniscuti = jz.id
                INNER JOIN (
                    SELECT empno, MAX(mutdt) AS max_mutdt
                    FROM hirarki
                    GROUP BY empno
                ) max_hirarki ON pc.empno = max_hirarki.empno
                INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                 WHERE  approval_status  LIKE '%1%' AND pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NULL AND pc.approval_status != '-1'
            ) AS numbered
            WHERE RowNum = 1
        ) AS final_query;
            "
        ));
        $jumlahpengajuancuti3 = !empty($result7) ? (string) $result7[0]->jumlahpengajuancuti3 : '0';
        $pengajuanCutiList1 = !empty($result7) ? implode(', ', array_column($result7, 'nama_karyawan4')) : '0';

        $result3 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT final_query.jumlahpengajuanizin1, final_query.empnm AS nama_karyawan1
        FROM (
            SELECT
                COUNT(*) OVER () AS jumlahpengajuanizin1,
                id,
                empno,
                tgl_mulai,
                tgl_selesai,
                pjenisizin,
                tgl_pengajuan,
                approval1_status,
                approval1_id,
                approval2_id,
                approval_status,
                jenisizin,
                note,
                empnm,
                hirar
            FROM (
                SELECT
                    pc.id,
                    pc.empno,
                    pc.tgl_mulai,
                    pc.tgl_selesai,
                    pc.pjenisizin,
                    pc.tgl_pengajuan,
                    pc.approval1_status,
                    pc.approval1_id,
                    pc.approval2_id,
                    pc.approval_status,
                    pc.note,
                    jz.jenisizin,
                    e.empnm,
                    h.hirar,
                    ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                FROM pengajuanizin pc
                INNER JOIN employee e ON pc.empno = e.empno
                INNER JOIN jenisizin jz ON pc.pjenisizin = jz.id
                INNER JOIN (
                    SELECT empno, MAX(mutdt) AS max_mutdt
                    FROM hirarki
                    GROUP BY empno
                ) max_hirarki ON pc.empno = max_hirarki.empno
                INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                WHERE pc.approval_status IN (0, 1, 2)
            ) AS numbered
            WHERE RowNum = 1
        ) AS final_query;

            "
        ));
        $jumlahpengajuanizin1 = !empty($result3) ? (string) $result3[0]->jumlahpengajuanizin1 : '0';
        $nama_karyawan1 = !empty($result3) ? implode(', ', array_column($result3, 'nama_karyawan1')) : '0';

        $result4 = DB::connection('mysql2')->select(DB::raw(
            "
           SELECT final_query.jumlahpengajuanizin2, final_query.empnm AS nama_karyawan
            FROM (
                SELECT
                    COUNT(*) OVER () AS jumlahpengajuanizin2,
                    id,
                    empno,
                    tgl_mulai,
                    tgl_selesai,
                    pjenisizin,
                    tgl_pengajuan,
                    approval1_status,
                    approval1_id,
                    approval2_id,
                    approval_status,
                    jenisizin,
                    note,
                    empnm,
                    hirar
                FROM (
                    SELECT
                        pc.id,
                        pc.empno,
                        pc.tgl_mulai,
                        pc.tgl_selesai,
                        pc.pjenisizin,
                        pc.tgl_pengajuan,
                        pc.approval1_status,
                        pc.approval1_id,
                        pc.approval2_id,
                        pc.approval_status,
                        pc.note,
                        jz.jenisizin,
                        e.empnm,
                        h.hirar,
                        ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                    FROM pengajuanizin pc
                    INNER JOIN employee e ON pc.empno = e.empno
                    INNER JOIN jenisizin jz ON pc.pjenisizin = jz.id
                    INNER JOIN (
                        SELECT empno, MAX(mutdt) AS max_mutdt
                        FROM hirarki
                        GROUP BY empno
                    ) max_hirarki ON pc.empno = max_hirarki.empno
                    INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                     WHERE  approval_status  LIKE '%0%' AND pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NULL
                ) AS numbered
                WHERE RowNum = 1
            ) AS final_query;

            "
        ));
        $jumlahpengajuanizin2 = !empty($result4) ? (string) $result4[0]->jumlahpengajuanizin2 : '0';
        $pengajuanIzinList = !empty($result4) ? implode(', ', array_column($result4, 'nama_karyawan')) : '0';

        $result8 = DB::connection('mysql2')->select(DB::raw(
            "
           SELECT final_query.jumlahpengajuanizin3, final_query.empnm AS nama_karyawan3
            FROM (
                SELECT
                    COUNT(*) OVER () AS jumlahpengajuanizin3,
                    id,
                    empno,
                    tgl_mulai,
                    tgl_selesai,
                    pjenisizin,
                    tgl_pengajuan,
                    approval1_status,
                    approval1_id,
                    approval2_id,
                    approval_status,
                    jenisizin,
                    note,
                    empnm,
                    hirar
                FROM (
                    SELECT
                        pc.id,
                        pc.empno,
                        pc.tgl_mulai,
                        pc.tgl_selesai,
                        pc.pjenisizin,
                        pc.tgl_pengajuan,
                        pc.approval1_status,
                        pc.approval1_id,
                        pc.approval2_id,
                        pc.approval_status,
                        pc.note,
                        jz.jenisizin,
                        e.empnm,
                        h.hirar,
                        ROW_NUMBER() OVER (PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                    FROM pengajuanizin pc
                    INNER JOIN employee e ON pc.empno = e.empno
                    INNER JOIN jenisizin jz ON pc.pjenisizin = jz.id
                    INNER JOIN (
                        SELECT empno, MAX(mutdt) AS max_mutdt
                        FROM hirarki
                        GROUP BY empno
                    ) max_hirarki ON pc.empno = max_hirarki.empno
                    INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                   WHERE  approval_status  LIKE '%1%' AND pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NULL AND pc.approval_status != '-1'
                ) AS numbered
                WHERE RowNum = 1
            ) AS final_query;

            "
        ));
        $jumlahpengajuanizin3 = !empty($result8) ? (string) $result8[0]->jumlahpengajuanizin3 : '0';
        $pengajuanIzinList1 = !empty($result8) ? implode(', ', array_column($result8, 'nama_karyawan3')) : '0';
        $result5 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT
                SUM(CASE WHEN table_name = 'pengajuancuti' AND approval_status = '1' THEN 1 ELSE 0 END) AS approved_cuti,
                SUM(CASE WHEN table_name = 'pengajuancuti' AND approval_status = '0' THEN 1 ELSE 0 END) AS pending_cuti,
                SUM(CASE WHEN table_name = 'pengajuancuti' AND (approval_status = '-1' OR approval_status = '-2') THEN 1 ELSE 0 END) AS rejected_cuti,
                SUM(CASE WHEN table_name = 'pengajuanizin' AND approval_status = '1' THEN 1 ELSE 0 END) AS approved_izin,
                SUM(CASE WHEN table_name = 'pengajuanizin' AND approval_status = '0' THEN 1 ELSE 0 END) AS pending_izin,
                SUM(CASE WHEN table_name = 'pengajuanizin' AND (approval_status = '-1' OR approval_status = '-2') THEN 1 ELSE 0 END) AS rejected_izin
            FROM (
                SELECT 'pengajuancuti' AS table_name, approval_status FROM pengajuancuti
                UNION ALL
                SELECT 'pengajuanizin' AS table_name, approval_status FROM pengajuanizin
            ) AS combined_data;
            "
        ));

        $approvedCutiRequests = $result5[0]->approved_cuti;
        $pendingCutiRequests = $result5[0]->pending_cuti;
        $rejectedCutiRequests = $result5[0]->rejected_cuti;

        $approvedIzinRequests = $result5[0]->approved_cuti;
        $pendingIzinRequests = $result5[0]->pending_cuti;
        $rejectedIzinRequests = $result5[0]->rejected_cuti;

        // Compile all the data into an array
        $dashboardData = [
            'approvedCutiRequests' => $approvedCutiRequests,
            'pendingCutiRequests' => $pendingCutiRequests,
            'rejectedCutiRequests' => $rejectedCutiRequests,
            'approvedIzinRequests' => $approvedIzinRequests,
            'pendingIzinRequests' => $pendingIzinRequests,
            'rejectedIzinRequests' => $rejectedIzinRequests,
        ];
        // Pass the string saldoCuti to the view.
        return view('dashboard', compact('saldocutitahunan', 'saldocutiistimewa', 'jumlahpengajuancuti1', 'nama_karyawan', 'nama_karyawan1', 'jumlahpengajuancuti2', 'jumlahpengajuancuti3', 'jumlahpengajuanizin1', 'jumlahpengajuanizin2', 'jumlahpengajuanizin3', 'dashboardData', 'pengajuanCutiList', 'pengajuanIzinList', 'pengajuanCutiList1', 'pengajuanIzinList1'));
    }

    public function getDashboardData()
    {
        // Total requests count
        $totalCutiRequests = DB::table('pengajuancuti')->count();

        // Count of approved requests
        $approvedCutiRequests = DB::table('pengajuancuti')
            ->where('approval_status', '1') // assuming '1' means approved by atasan1
            ->orWhere('approval_status', '2') // assuming '2' means approved by atasan2
            ->count();

        // Count of pending requests
        $pendingCutiRequests = DB::table('pengajuancuti')
            ->where('approval_status', '0') // assuming '0' means created/awaiting approval
            ->count();

        // Count of rejected requests
        $rejectedCutiRequests = DB::table('pengajuancuti')
            ->where('approval_status', '-1') // assuming '-1' means rejected by atasan1
            ->orWhere('approval_status', '-2') // assuming '-2' means rejected by atasan2
            ->count();

        // Compile all the data into an array
        $dashboardData = [
            'approvedCutiRequests' => $approvedCutiRequests,
            'pendingCutiRequests' => $pendingCutiRequests,
            'rejectedCutiRequests' => $rejectedCutiRequests,
        ];

        // Pass the data to the view
        return view('dashboard', compact('dashboardData'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function approve(Request $request)
    // {
    //     $npk = auth()->user()->npk;
    //     $pengajuanCuti = PengajuanCuti::where('id', $request->id)->first();
    //     if ($pengajuanCuti->approval1_id == $npk) {
    //         $pengajuanCuti->approval1_status = Carbon::now();
    //         if ($request->status == '0') {
    //             $pengajuanCuti->approval_status = '-1';
    //             $pengajuanCuti->reason_approval1 = $request->reason;
    //         } else {
    //             $pengajuanCuti->approval_status = '1';
    //         }
    //     } else if ($pengajuanCuti->approval2_id == $npk) {
    //         $pengajuanCuti->approval2_status = Carbon::now();
    //         if ($request->status == '0') {
    //             $pengajuanCuti->approval_status = '-2';
    //             $pengajuanCuti->reason_approval2 = $request->reason;
    //         } else {
    //             $pengajuanCuti->approval_status = '2';
    //         }
    //     }
    //     $pengajuanCuti->save();

    //     return redirect()->back()->with([
    //         'success' => true
    //     ]);
    // }
    public function approve(Request $request)
    {
        $npk = auth()->user()->npk;
        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT kehadiranmu.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr, users.is_admin
            FROM kehadiranmu
            LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
            LEFT JOIN users ON kehadiranmu.empno = users.npk
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiranmu.empno = $npk
            GROUP BY kehadiranmu.empno, hirarki.hirar, hirarkidesc.descr, users.is_admin
            ORDER BY mutdt DESC LIMIT 1;
            "
        ));
        $isadmin = trim($userInfo[0]->is_admin);

        $pengajuanCuti = PengajuanCuti::where('id', $request->id)->first();

        if ($pengajuanCuti->approval1_id == $npk) {
            if ($request->status == '0') {
                $pengajuanCuti->reason_approval1 = $request->reason;
                $pengajuanCuti->approval_status = '-1';
                session()->flash('status', 'Pengajuan cuti ditolak.');
            } else {
                $pengajuanCuti->approval_status = '1';
                session()->flash('status', 'Pengajuan cuti disetujui.');
            }
            $pengajuanCuti->approval1_status = Carbon::now();
        } else if ($pengajuanCuti->approval2_id == $npk) {
            if ($request->status == '0') {
                $pengajuanCuti->reason_approval2 = $request->reason;
                $pengajuanCuti->approval_status = '-2';
                session()->flash('status', 'Pengajuan cuti ditolak.');
            } else {
                $pengajuanCuti->approval_status = '2';
                session()->flash('status', 'Pengajuan cuti disetujui.');
            }
            $pengajuanCuti->approval2_status = Carbon::now();
        }

        if ($isadmin == '1') {
            if ($request->status == '0') {
                $pengajuanCuti->approval_status = '-3';
                $pengajuanCuti->reason_approvalhr = $request->reason;
                session()->flash('status', 'Pengajuan cuti ditolak oleh HR.');
            } else {
                $pengajuanCuti->approval_status = '3';
                $total_hari_cuti = $pengajuanCuti->total_hari;
                switch ($pengajuanCuti->jeniscuti) {
                    case 3:
                        $maxEnddt = DB::connection('mysql2')
                            ->table('pengajuancutikar')
                            ->where('empno', 'LIKE', $pengajuanCuti->empno)
                            ->max('enddt');

                        DB::connection('mysql2')
                            ->table('pengajuancutikar')
                            ->where('empno', 'LIKE', $pengajuanCuti->empno)
                            ->where('enddt', $maxEnddt)
                            ->increment('clget', $total_hari_cuti);
                        break;
                    case 9:
                        $currentYear = date('Y');
                        DB::connection('mysql2')->table('pengajuancutikar')
                            ->where('empno', 'LIKE', $pengajuanCuti->empno)
                            ->where('enddt', 'LIKE', "%$currentYear%")
                            ->increment('clget', $total_hari_cuti);
                        break;
                    default:
                        break;
                }
                session()->flash('status', 'Pengajuan cuti disetujui oleh HR.');
            }
        }

        $pengajuanCuti->save();

        return redirect()->back()->with([
            'success' => 'Process completed successfully.'
        ]);
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
