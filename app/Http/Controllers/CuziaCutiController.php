<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
// use App\Models\PengajuanCuti;
use App\Models\pengajuancuti_document;

class CuziaCutiController extends Controller
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

        return view('cuziacuti', compact('userInfoOccupation', 'userInfoDept'));
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
                WHERE pc.empno LIKE '%$npk%'

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
        $is_admin = auth()->user()->is_admin;
        if ($is_admin == 1) {
            $data = PengajuanCuti::where('approval_status', '2');
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

    public function store(Request $request)
    {
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

        $cleanedStringDept = trim($userInfo[0]->hirar);
        // Mengurangi dua digit terakhir dari string
        $tempapprov1 = substr($cleanedStringDept, 0, -2);

        $approval1Result = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT
            empno,
            MAX(hirar) AS hirar
          FROM
            hirarki
          WHERE
            hirar = '$tempapprov1'
          GROUP BY
            empno
          ORDER BY
            MAX(mutdt) DESC

            "
        ));

        // $approval2Result now contains the empno with the second largest mutdt for the same hirar as $tempapprov1
        // $approv1 = trim($approval3Result[0]->empno);
        $tempapprov2 = substr($cleanedStringDept, 0, -4);
        $approval2Result = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT empno
            FROM hirarki
            WHERE hirar = '$tempapprov2'
            GROUP BY empno
            ORDER BY MAX(mutdt) DESC
            LIMIT 1
            "
        ));
        // dd($approval1);
        $cuti = new PengajuanCuti();
        $cuti->empno = $request->input('empno');
        $cuti->tgl_pengajuan = date('d-m-Y'); // Menyimpan tanggal hari ini
        $cuti->kodepengajuan = 'CUTI' . date('ymdHi') . trim($npk) . chr(rand(65, 90));

        // Check if approval1Result has 2 hirars
        // Check if approval1Result has 2 hirars
        if (count($approval1Result) > 1) {
            // There are multiple hirars, find the longest one
            $maxLength = 0;
            $longestHirar = '';

            // Find the longest hirar
            foreach ($approval1Result as $result) {
                $hirarLength = strlen($result->hirar);
                if ($hirarLength > $maxLength) {
                    $maxLength = $hirarLength;
                    $longestHirar = $result->hirar;
                }
            }

            // Use the longest hirar for further processing
            $tempapprov1 = $longestHirar;

            // Query to get empno with the second largest mutdt for the same hirar as $tempapprov1
            $approval3Result = DB::connection('mysql2')->select(DB::raw(
                "
                SELECT MAX(mutdt) AS second_largest_mutdt, empno
                FROM hirarki
                WHERE hirar = '$tempapprov1'
                AND empno != (
                    SELECT empno
                    FROM hirarki
                    WHERE hirar = '$tempapprov1'
                    GROUP BY empno
                    ORDER BY MAX(mutdt) DESC
                    LIMIT 1
                )
                GROUP BY empno
                ORDER BY MAX(mutdt) DESC
                LIMIT 1
                "
            ));

                // Periksa apakah ada hasil yang ditemukan
                if (!empty($approval3Result)) {
                    // Ambil nilai empno dari objek pertama dalam hasil
                    $approval1 = $approval3Result[0]->empno;

                    // Check if approval1_id is same as logged in user's NPK
                    if ($approval1 == $npk) {
                        // If approval1_id is same as logged in user's NPK,
                        // perform additional check to find third largest mutdt
                        $thirdLargestResult = DB::connection('mysql2')->select(DB::raw(
                            "
                            SELECT MAX(mutdt) AS third_largest_mutdt, empno
                            FROM hirarki
                            WHERE hirar = '$tempapprov1'
                            AND empno != '$npk' -- Exclude logged in user's NPK
                            GROUP BY empno
                            ORDER BY MAX(mutdt) DESC
                            LIMIT 1
                            "
                        ));

                        // Periksa apakah ada hasil yang ditemukan untuk third largest
                        if (!empty($thirdLargestResult)) {
                            // Ambil nilai empno dari hasil
                            $approval1 = $thirdLargestResult[0]->empno;
                            $cuti->approval1_id = $approval1;
                        } else {
                            // Jika tidak ada third largest, set approval1_id menjadi null
                            $cuti->approval1_id = null;
                        }
                    } else {
                        // Jika approval1_id tidak sama dengan NPK yang login, set approval1_id ke nilai yang ditemukan
                        $cuti->approval1_id = $approval1;
                    }
                } else {
                    // Setel nilai menjadi null atau sesuai kebutuhan jika tidak ada hasil yang ditemukan
                    $cuti->approval1_id = null;
                }
            } else {
                // There is only one hirar, use the result from approval1Result
                $cuti->approval1_id = $approval1Result[0]->empno;
            }


        // Periksa apakah ada hasil yang ditemukan
        if (!empty($approval2Result)) {
            // Ambil nilai empno dari objek pertama dalam hasil
            $approval2 = $approval2Result[0]->empno;
            // Setelah memastikan $approval1 adalah string, Anda dapat memberikannya ke properti atau variabel yang diharapkan bertipe string
            $cuti->approval2_id = $approval2;
        } else {
            // Setel nilai menjadi null atau sesuai kebutuhan jika tidak ada hasil yang ditemukan
            $cuti->approval2_id = null;
        }
        $cuti->tgl_mulai = $request->input('tgl_mulai');
        $cuti->tgl_selesai = $request->input('tgl_selesai');
        $cuti->jeniscuti = $request->input('jenis_cuti');
        $cuti->note = $request->input('note');
        $cuti->approval_status = '0';
        // dd($approval1);
        $cuti->save();
        // Redirect to the index view after successful form submission
        return redirect()->route('cuziacuti.index')->with('success', 'Pengajuan cuti berhasil disimpan.');
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
