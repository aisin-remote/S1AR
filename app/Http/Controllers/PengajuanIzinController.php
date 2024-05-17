<?php

namespace App\Http\Controllers;

use App\Models\jenisizin;
use Carbon\Carbon;
use App\Models\saldoIzin;
use Illuminate\Http\Request;
use Termwind\Components\Raw;
use App\Models\PengajuanIzin;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class PengajuanIzinController extends Controller
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
        $jenisIzin = jenisizin::select('id', 'jenisizin')
            ->where('jenisizin', 'NOT LIKE', '%Cuti%')
            ->get();
        return view('izin', compact('userInfoOccupation', 'userInfoDept', 'jenisIzin'));
        // dd($request->all());
    }

    public function getData(Request $request)
    {

        $tanggalSekarang = Carbon::now()->format('Ymd');

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
                WHERE (pc.approval1_id LIKE '%$npk%' AND pc.approval1_status IS NULL) OR (pc.approval2_id LIKE '%$npk%' AND pc.approval2_status IS NULL)
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
        //     $data = PengajuanIzin::where('approval_status', '2');
        // }
        // dd($data);
        return DataTables::of($data)->make(true);
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function approve(Request $request)
    // {
    //     $npk = auth()->user()->npk;
    //     $pengajuanIzin = PengajuanIzin::where('id', $request->id)->first();
    //     if ($pengajuanIzin->approval1_id == $npk) {
    //         $pengajuanIzin->approval1_status = Carbon::now();
    //         if ($request->status == '0') {
    //             $pengajuanIzin->approval_status = '-1';
    //             $pengajuanIzin->reason_approval1 = $request->reason;
    //         } else {
    //             $pengajuanIzin->approval_status = '1';
    //         }
    //     } else if ($pengajuanIzin->approval2_id == $npk) {
    //         $pengajuanIzin->approval2_status = Carbon::now();
    //         if ($request->status == '0') {
    //             $pengajuanIzin->approval_status = '-2';
    //             $pengajuanIzin->reason_approval2 = $request->reason;
    //         } else {
    //             $pengajuanIzin->approval_status = '2';
    //         }
    //     }
    //     $pengajuanIzin->save();

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

        $pengajuanIzin = PengajuanIzin::where('id', $request->id)->first();

        // Check if the current user is the first approver
        if ($pengajuanIzin->approval1_id == $npk) {
            if ($request->status == '0') { // If rejected
                $pengajuanIzin->reason_approval1 = $request->reason; // Save the rejection reason
                $pengajuanIzin->approval_status = '-1';
            } else {
                $pengajuanIzin->approval_status = '1';
            }
            $pengajuanIzin->approval1_status = Carbon::now();
        }
        // Check if the current user is the second approver
        else if ($pengajuanIzin->approval2_id == $npk) {
            if ($request->status == '0') { // If rejected
                $pengajuanIzin->reason_approval2 = $request->reason; // Save the rejection reason
                $pengajuanIzin->approval_status = '-2';
            } else {
                $pengajuanIzin->approval_status = '2';
            }
            $pengajuanIzin->approval2_status = Carbon::now();
        }

        // Additional check for admin approval or rejection
        if ($isadmin == '1') {
            if ($request->status == '0') { // If rejected by admin
                $pengajuanIzin->approval_status = '-3';
                $pengajuanIzin->reason_approvalhr = $request->reason;
            } else { // If approved by admin
                $pengajuanIzin->approval_status = '3';
                // Tambahkan total hari izin ke clget
            }
        }


        $pengajuanIzin->save();

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
