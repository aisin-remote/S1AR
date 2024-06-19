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


class CuziaController extends Controller
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
        return view('cuzia', compact('userInfoOccupation', 'userInfoDept', 'jenisizin'));
        // dd($request->all());
    }

    public function getData(Request $request)
    {

        $tanggalSekarang = Carbon::now()->format('Ymd');

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
        $isadmin = ($userInfo[0]->is_admin);
        // $cleanedStringDeptFinal = substr($cleanedStringDept, 0, 3);
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Memeriksa apakah data tanggal tersedia
        if (!empty($start_date) && !empty($end_date)) {
            // Memproses data tanggal jika ada
            $tanggalMulai = Carbon::parse($start_date)->format('d-m-Y');
            $tanggalAkhir = Carbon::parse($end_date)->format('d-m-Y');
        } else {
            // Menggunakan tanggal sekarang jika tidak ada tanggal yang diberikan
            $tanggalMulai = Carbon::now()->format('d-m-Y');
            $tanggalAkhir = Carbon::now()->format('d-m-Y');
        }

        if ($userInfo[0]->is_admin == 1) {
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_pengajuan_prev = NULL');
            // Execute main query
            $data = DB::connection('mysql2')
                ->select(DB::raw("
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
                WHERE pc.approval_status IN (0, 1, 2)
            ) AS numbered
            WHERE RowNum = 1
            ORDER BY empno ASC, tgl_mulai DESC;
                    "));
        } else {
            DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_pengajuan_prev = NULL');

            // Execute main query
            $data = DB::connection('mysql2')
                ->select(DB::raw("
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
                WHERE (pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NULL)
                OR (pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NULL)
            ) AS numbered
            WHERE RowNum = 1
            ORDER BY empno ASC, tgl_mulai DESC;

                "));
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
        // $is_admin = auth()->user()->is_admin;
        // if ($is_admin == 1) {
        //     $data = PengajuanCuti::where('approval_status', '2');
        // }
        // dd($data);
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
            SELECT COUNT(*) AS jumlahpengajuancuti1
            FROM (
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
                    WHERE pc.approval_status IN (0, 1, 2)
                ) AS numbered
                WHERE RowNum = 1
            ) AS final_query;
            "
        ));
        $jumlahpengajuancuti1 = !empty($result1) ? (string) $result1[0]->jumlahpengajuancuti1 : '0';

        $result2 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT COUNT(*) AS jumlahpengajuancuti2
            FROM (
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
                    WHERE (pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NULL)
                        OR (pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NULL)
                ) AS numbered
                WHERE RowNum = 1
            ) AS final_query;
            "
        ));
        $jumlahpengajuancuti2= !empty($result2) ? (string) $result2[0]->jumlahpengajuancuti2 : '0';

        $result3 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT COUNT(*) AS jumlahpengajuanizin1
            FROM (
                SELECT
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
                    lampiran,
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
                        pc.lampiran,
                        pc.note,
                        jz.jenisizin,
                        e.empnm,
                        h.hirar,
                        ROW_NUMBER() OVER(PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
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
        $result4 = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT COUNT(*) AS jumlahpengajuanizin2
            FROM (
                SELECT
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
                    lampiran,
                    note,
                    jenisizin,
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
                        pc.pjenisizin,
                        pc.tgl_pengajuan,
                        pc.approval1_status,
                        pc.approval1_id,
                        pc.approval2_id,
                        pc.approval_status,
                        pc.lampiran,
                        pc.note,
                        jz.jenisizin,
                        u.is_admin,
                        e.empnm,
                        h.hirar,
                        h.mutdt,
                        hd.descr,
                        ROW_NUMBER() OVER(PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
                    FROM pengajuanizin pc
                    INNER JOIN employee e ON pc.empno = e.empno
                    INNER JOIN jenisizin jz ON pc.pjenisizin = jz.id
                    INNER JOIN users u ON pc.empno = u.npk
                    INNER JOIN (
                        SELECT empno, MAX(mutdt) AS max_mutdt
                        FROM hirarki
                        GROUP BY empno
                    ) max_hirarki ON pc.empno = max_hirarki.empno
                    INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                    INNER JOIN hirarkidesc hd ON h.hirar = hd.hirar
                    WHERE (pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NULL)
                    OR (pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NULL)
                ) AS numbered
                WHERE RowNum = 1
            ) AS final_query;

            "
        ));
        $jumlahpengajuanizin2 = !empty($result4) ? (string) $result4[0]->jumlahpengajuanizin2 : '0';

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
        return view('dashboard', compact('saldocutitahunan', 'saldocutiistimewa', 'jumlahpengajuancuti1', 'jumlahpengajuancuti2','jumlahpengajuanizin1','jumlahpengajuanizin2', 'dashboardData'));
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

        // Check if the current user is the first approver
        if ($pengajuanCuti->approval1_id == $npk) {
            if ($request->status == '0') { // If rejected
                $pengajuanCuti->reason_approval1 = $request->reason; // Save the rejection reason
                $pengajuanCuti->approval_status = '-1';
            } else {
                $pengajuanCuti->approval_status = '1';
            }
            $pengajuanCuti->approval1_status = Carbon::now();
        }
        // Check if the current user is the second approver
        else if ($pengajuanCuti->approval2_id == $npk) {
            if ($request->status == '0') { // If rejected
                $pengajuanCuti->reason_approval2 = $request->reason; // Save the rejection reason
                $pengajuanCuti->approval_status = '-2';
            } else {
                $pengajuanCuti->approval_status = '2';
            }
            $pengajuanCuti->approval2_status = Carbon::now();
        }

        // Additional check for admin approval or rejection
        if ($isadmin == '1') {
            if ($request->status == '0') { // If rejected by admin
                $pengajuanCuti->approval_status = '-3';
                $pengajuanCuti->reason_approvalhr = $request->reason;
            } else { // If approved by admin
                $pengajuanCuti->approval_status = '3';
                $total_hari_cuti = $pengajuanCuti->total_hari;
                // Tambahkan total hari cuti ke clget
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
                            ->increment('clget', $total_hari_cuti); // Menggunakan increment untuk menambah nilai clget sebanyak $total_hari_cuti
                        break;

                    case 9:
                        $currentYear = date('Y');
                        // Lakukan perubahan pada tabel pengajuancutikar
                        DB::connection('mysql2')->table('pengajuancutikar')
                            ->where('empno', 'LIKE', $pengajuanCuti->empno)
                            ->where('enddt', 'LIKE', "%$currentYear%")
                            ->increment('clget', $total_hari_cuti); // Menggunakan increment untuk menambah nilai clget sebanyak $total_hari_cuti
                        break;
                    default:
                        // Jika jenis cuti tidak dikenali
                        break;
                }
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
