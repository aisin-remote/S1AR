<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\saldoCuti;
use Illuminate\Http\Request;
use Termwind\Components\Raw;
use App\Models\PengajuanCuti;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class RekapCutiController extends Controller
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

        return view('rekapcuti', compact('userInfoOccupation', 'userInfoDept'));
        // dd($request->all());
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

        //     return DataTables::of()->make(true);
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

        DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_mulai_prev = NULL');

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
            note,
            empnm,
            hirar,
            mutdt,
            descr
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
                e.empnm,
                h.hirar,
                h.mutdt,
                hd.descr,
                @row_number := CASE
                    WHEN pc.empno != @empno_prev OR pc.tgl_mulai != @tgl_mulai_prev
                        THEN 1
                        ELSE @row_number + 1
                    END AS RowNum,
                @empno_prev := pc.empno,
                @tgl_mulai_prev := pc.tgl_mulai
            FROM pengajuancuti pc
            INNER JOIN employee e ON pc.empno = e.empno
            INNER JOIN (
                SELECT empno, MAX(mutdt) AS max_mutdt
                FROM hirarki
                GROUP BY empno
            ) max_hirarki ON pc.empno = max_hirarki.empno
            INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
            INNER JOIN hirarkidesc hd ON h.hirar = hd.hirar
            WHERE  pc.approval_status LIKE 2
        ) AS numbered
        WHERE RowNum = 1
        ORDER BY empno ASC, tgl_mulai DESC, tgl_pengajuan DESC;
                "));

        // Mengubah format tanggal dan jam dalam hasil data
        foreach ($data as $row) {
            if ($row->tgl_mulai != "        ") {
                // $row->tgl_mulai = substr($row->tgl_mulai, 0, 4) . '-' . substr($row->tgl_mulai, 4, 2) . '-' . substr($row->tgl_mulai, 6, 2);
                $row->tgl_mulai = substr($row->tgl_mulai, 0, 10);
                $row->tgl_pengajuan = substr($row->tgl_pengajuan, 0, 10);
            } else {
                $row->tgl_mulai = "Tidak Ada Data";
                $row->tgl_pengajuan = "Tidak Ada Data";
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
        // $is_admin = auth()->user()->is_admin;
        // if ($is_admin == 1) {
        //     $data = PengajuanCuti::where('approval_status', '2');
        // }

        return DataTables::of($data)->make(true);
    }


    public function saldoCuti(Request $request)
    {
        // Your existing code to get the NPK of the logged-in user.
        $npk = $request->user()->npk;

        // Your existing code to retrieve the saldo cuti data.
        $result = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT CONVERT((clrig - clget), CHAR) AS saldo_cuti
            FROM pengajuancutikar
            WHERE pengajuancutikar.empno = $npk
            ORDER BY expdt DESC
            LIMIT 1;
            "
        ));

        // Check if the result is not empty and get the first element of the array.
        $saldoCuti = !empty($result) ? (string) $result[0]->saldo_cuti : '0';

        // Pass the string saldoCuti to the view.
        return view('dashboard', compact('saldoCuti'));
    }
    public function chartData()
    {
        $currentYear = Carbon::now()->year;
        $absenceCounts = [];

        // Retrieve counts for each type of absence (Cuti, Sakit, Ijin) by month.
        $types = [
            'CTH' => 'Cuti',
            'SKT' => 'Sakit',
            'DLU' => 'Izin'
        ];

        foreach ($types as $code => $type) {
            $absenceCounts[$type] = DB::table('kehadiranmu')
                ->select(DB::raw('MONTH(schdt) as month'), DB::raw('COUNT(*) as count'))
                ->where('rsccd', $code)
                ->whereYear('schdt', $currentYear)
                ->groupBy(DB::raw('MONTH(schdt)'))
                ->orderBy('month', 'ASC')
                ->pluck('count', 'month')
                ->toArray();

            // Ensure that each month has a value.
            for ($month = 1; $month <= 12; $month++) {
                if (!array_key_exists($month, $absenceCounts[$type])) {
                    $absenceCounts[$type][$month] = 0;
                }
            }

            ksort($absenceCounts[$type]); // Sort by month.
        }
        $chartData = $absenceCounts;
        // Pass the data to the view.
        return view('dashboard', compact('chartData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request)
    {
        $npk = auth()->user()->npk;
        $pengajuanCuti = PengajuanCuti::where('id', $request->id)->first();
        if ($pengajuanCuti->approval1_id == $npk) {
            $pengajuanCuti->approval1_status = Carbon::now();
            if ($request->status == '0') {
                $pengajuanCuti->approval_status = '-1';
            } else {
                $pengajuanCuti->approval_status = '1';
            }
        } else if ($pengajuanCuti->approval2_id == $npk) {
            $pengajuanCuti->approval2_status = Carbon::now();
            if ($request->status == '0') {
                $pengajuanCuti->approval_status = '-2';
            } else {
                $pengajuanCuti->approval_status = '2';
            }
        }
        $pengajuanCuti->save();

        return redirect()->back()->with([
            'success' => true
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
