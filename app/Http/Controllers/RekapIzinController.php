<?php

namespace App\Http\Controllers;

use App\Models\jenisizin;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
// use App\Models\Pengajuanizin;
use App\Models\PengajuanIzin_Document;

class RekapIzinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tanggalSekarang = Carbon::now()->format('Y-m-d');

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
            ->where('jenisizin', 'NOT LIKE', '%Cuti%')
            ->get();
        return view('rekapizin', compact('userInfoOccupation', 'userInfoDept', 'jenisizin'));
        // dd($request->all());
    }

    public function getData(Request $request)
    {
        // Current date
        $tanggalSekarang = Carbon::now()->format('Y-m-d');

        // Get user's npk (assuming it's retrieved correctly)
        $npk = auth()->user()->npk;

        // Fetch user information (hirar) based on npk
        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "SELECT kehadiranmu.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
        FROM kehadiranmu
        LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
        LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
        WHERE kehadiranmu.empno = $npk
        GROUP BY kehadiranmu.empno, hirarki.hirar, hirarkidesc.descr
        ORDER BY mutdt DESC LIMIT 1;"
        ));

        // Initialize jenis with default value
        $jenis = 'Jenis tidak dikenali';

        if (!empty($userInfo)) {
            // Determine jenis based on hirar length
            $cleanedString = str_replace(' ', '', $userInfo[0]->hirar);
            $jumlahKarakter = strlen($cleanedString);

            if ($jumlahKarakter == 5) {
                $jenis = 'KDP';
            } elseif ($jumlahKarakter == 7) {
                $jenis = 'SPV';
            } elseif ($jumlahKarakter == 9) {
                $jenis = 'LDR/OPR';
            } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                $jenis = 'GMR';
            }
        }

        // Handle date inputs from request
        $tanggalMulai = $request->input('start_date') ?? $tanggalSekarang;
        $tanggalAkhir = $request->input('end_date') ?? $tanggalSekarang;

        // Query to fetch data similar to the first example
        $data = DB::connection('mysql2')->select(DB::raw("
        SELECT
            empno,
            tgl_mulai,
            tgl_selesai,
            pjenisizin,
            tgl_pengajuan,
            approval1_status,
            approval_status,
            lampiran,
            note,
            jenisizin,
            empnm,
            hirar
        FROM (
            SELECT
                pc.empno,
                pc.tgl_mulai,
                pc.tgl_selesai,
                pc.pjenisizin,
                pc.tgl_pengajuan,
                pc.approval1_status,
                pc.approval_status,
                pc.lampiran,
                pc.note,
                jz.jenisizin,
                e.empnm,
                h.hirar,
                ROW_NUMBER() OVER(PARTITION BY pc.empno, pc.tgl_pengajuan ORDER BY pc.tgl_mulai DESC) AS RowNum
            FROM pengajuanizin pc
            INNER JOIN employee e ON pc.empno = e.empno
            INNER JOIN jenisizin jz ON pc.pjenisizin = jz.id
            INNER JOIN (
                SELECT empno, MAX(mutdt) AS max_mutdt
                FROM hirarki
                GROUP BY empno
            ) max_hirarki ON pc.empno = max_hirarki.empno
            INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
            WHERE pc.approval_status = 3
              AND (
                STR_TO_DATE(pc.tgl_pengajuan, '%d-%m-%Y') BETWEEN '$tanggalMulai' AND '$tanggalAkhir'
                OR '$tanggalMulai' = '$tanggalAkhir' -- To include all data if no date filter is applied
              )
        ) AS numbered
        WHERE RowNum = 1
        ORDER BY empno ASC, tgl_mulai DESC, tgl_pengajuan ASC;
    "));

        // Modify 'hirar' field based on jenis calculation
        foreach ($data as $row) {
            $cleanedString = str_replace(' ', '', $row->hirar);
            $jumlahKarakter = strlen($cleanedString);

            if ($jumlahKarakter == 5) {
                $row->hirar = 'KDP';
            } elseif ($jumlahKarakter == 7) {
                $row->hirar = 'SPV';
            } elseif ($jumlahKarakter == 9) {
                $row->hirar = 'LDR/OPR';
            } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
                $row->hirar = 'GMR';
            } else {
                $row->hirar = 'Jenis tidak dikenali';
            }
        }

        return DataTables::of($data)->make(true);
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
