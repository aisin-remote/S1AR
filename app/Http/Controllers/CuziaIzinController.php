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

class CuziaIzinController extends Controller
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
        return view('cuziaizin', compact('userInfoOccupation', 'userInfoDept', 'jenisizin'));
        // dd($request->all());
    }

    public function getData(Request $request)
    {

        $tanggalSekarang = Carbon::now()->format('Y-m-d');

        $npk = auth()->user()->npk;

        $userInfo = DB::connection('mysql2')->select(DB::raw(
            "
            SELECT kehadiranmu.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
            FROM kehadiranmu
            LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
            LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
            WHERE kehadiranmu.empno = :npk
            GROUP BY kehadiranmu.empno, hirarki.hirar, hirarkidesc.descr
            ORDER BY mutdt DESC LIMIT 1;
            "
        ), ['npk' => $npk]);

        if (!empty($userInfo)) {
            $npkDesc = $userInfo[0]->hirar;

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
        $userInfoOccupation = $jenis;
        $userInfoDept = $cleanedStringDept;

        // Handling date filters
        if ($request->input('start_date') && $request->input('end_date')) {
            $tanggalMulai = Carbon::parse($request->input('start_date'))->format('Y-m-d');
            $tanggalAkhir = Carbon::parse($request->input('end_date'))->format('Y-m-d');
        } elseif ($request->input('start_date') || $request->input('end_date')) {
            $tanggalMulai = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->format('Y-m-d') : $tanggalSekarang;
            $tanggalAkhir = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->format('Y-m-d') : $tanggalSekarang;
        } else {
            $tanggalMulai = null;
            $tanggalAkhir = null;
        }

        $query = "
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
                    pc.pjenisizin,
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
                WHERE pc.empno = :npk
        ";

        if ($tanggalMulai && $tanggalAkhir) {
            $query .= " AND STR_TO_DATE(pc.tgl_mulai, '%Y-%m-%d') BETWEEN :tanggalMulai AND :tanggalAkhir ";
        }

        $query .= "
            ) AS numbered
            WHERE RowNum = 1
            ORDER BY empno ASC, tgl_mulai DESC;
        ";

        $bindings = ['npk' => $npk];

        if ($tanggalMulai && $tanggalAkhir) {
            $bindings['tanggalMulai'] = $tanggalMulai;
            $bindings['tanggalAkhir'] = $tanggalAkhir;
        }

        $data = DB::connection('mysql2')->select(DB::raw($query), $bindings);

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
    // public function getData(Request $request)
    // {
    //     $tanggalSekarang = Carbon::now()->format('Y-m-d');
    //     $npk = Auth::user()->npk;

    //     // Fetch user information
    //     $userInfo = DB::connection('mysql2')->select(DB::raw("
    //         SELECT kehadiranmu.empno, hirarki.hirar, MAX(hirarki.mutdt) AS mutdt, hirarkidesc.descr
    //         FROM kehadiranmu
    //         LEFT JOIN hirarki ON kehadiranmu.empno = hirarki.empno
    //         LEFT JOIN hirarkidesc ON hirarki.hirar = hirarkidesc.hirar
    //         WHERE kehadiranmu.empno = ?
    //         GROUP BY kehadiranmu.empno, hirarki.hirar, hirarkidesc.descr
    //         ORDER BY mutdt DESC LIMIT 1;
    //     "), [$npk]);

    //     if (!empty($userInfo)) {
    //         $npkDesc = $userInfo[0]->hirar;
    //         $cleanedString = str_replace(' ', '', $npkDesc);
    //         $jumlahKarakter = strlen($cleanedString);

    //         // Tentukan jenis berdasarkan jumlah karakter
    //         if ($jumlahKarakter == 5) {
    //             $jenis = 'KDP';
    //         } elseif ($jumlahKarakter == 7) {
    //             $jenis = 'SPV';
    //         } elseif ($jumlahKarakter == 9) {
    //             $jenis = 'LDR/OPR';
    //         } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
    //             $jenis = 'GMR';
    //         } else {
    //             $jenis = 'Jenis tidak dikenali';
    //         }
    //     } else {
    //         $jenis = 'Jenis tidak dikenali';
    //     }

    //     $cleanedStringDept = trim($userInfo[0]->descr ?? '');
    //     $userInfoOccupation = $jenis;
    //     $userInfoDept = $cleanedStringDept;

    //     if ($request->input('start_date') && $request->input('end_date')) {
    //         $tanggalMulai = Carbon::parse($request->input('start_date'))->format('Y-m-d');
    //         $tanggalAkhir = Carbon::parse($request->input('end_date'))->format('Y-m-d');
    //     } elseif ($request->input('start_date') || $request->input('end_date')) {
    //         $tanggalMulai = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->format('Y-m-d') : $tanggalSekarang;
    //         $tanggalAkhir = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->format('Y-m-d') : $tanggalSekarang;
    //     } else {
    //         $tanggalMulai = '1900-01-01';  // Tampilkan semua data jika tidak ada filter
    //         $tanggalAkhir = $tanggalSekarang;
    //     }

    //     DB::connection('mysql2')->select('SET @row_number = 0, @empno_prev = NULL, @tgl_mulai_prev = NULL');

    //     // Execute main query
    //     $data = DB::connection('mysql2')
    //         ->select(DB::raw("
    //         SELECT *
    //         FROM (
    //             SELECT
    //                 pc.empno,
    //                 pc.tgl_mulai,
    //                 pc.tgl_selesai,
    //                 pc.pjenisizin,
    //                 pc.tgl_pengajuan,
    //                 pc.approval1_status,
    //                 pc.approval_status,
    //                 pc.lampiran,
    //                 pc.note,
    //                 jz.jenisizin,
    //                 e.empnm,
    //                 h.hirar,
    //                 h.mutdt,
    //                 hd.descr,
    //                 ROW_NUMBER() OVER(PARTITION BY pc.empno, pc.tgl_mulai ORDER BY pc.tgl_mulai DESC) AS RowNum
    //             FROM pengajuanizin pc
    //             INNER JOIN employee e ON pc.empno = e.empno
    //             INNER JOIN jenisizin jz ON pc.pjenisizin = jz.id
    //             INNER JOIN (
    //                 SELECT empno, MAX(mutdt) AS max_mutdt
    //                 FROM hirarki
    //                 GROUP BY empno
    //             ) max_hirarki ON pc.empno = max_hirarki.empno
    //             INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
    //             INNER JOIN hirarkidesc hd ON h.hirar = hd.hirar
    //             WHERE pc.empno = ? AND STR_TO_DATE(pc.tgl_mulai, '%Y-%m-%d') BETWEEN ? AND ?
    //         ) AS numbered
    //         WHERE RowNum = 1
    //         ORDER BY empno ASC, tgl_mulai DESC;
    //     "), [$npk, $tanggalMulai, $tanggalAkhir]);

    //     // Iterate through each row in the collection
    //     foreach ($data as $row) {
    //         $cleanedString = str_replace(' ', '', $row->hirar);
    //         $jumlahKarakter = strlen($cleanedString);

    //         if ($jumlahKarakter == 5) {
    //             $row->hirar = 'KDP';
    //         } elseif ($jumlahKarakter == 7) {
    //             $row->hirar = 'SPV';
    //         } elseif ($jumlahKarakter == 9) {
    //             $row->hirar = 'LDR/OPR';
    //         } elseif ($jumlahKarakter == 2 || $jumlahKarakter == 3) {
    //             $row->hirar = 'GMR';
    //         } else {
    //             $row->hirar = 'Jenis tidak dikenali';
    //         }
    //     }

    //     return DataTables::of($data)->make(true);
    // }
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
        $cuti = new PengajuanIzin();
        $cuti->empno = $request->input('empno');
        $cuti->tgl_pengajuan = date('d-m-Y'); // Menyimpan tanggal hari ini
        $cuti->kodepengajuan = 'IZIN' . date('ymdHi') . trim($npk) . chr(rand(65, 90));

        // Check if approval1Result has 2 hirars
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

            // Check if the empno from approval1 is the same as the logged in user's NPK
            if (!empty($approval3Result) && $approval3Result[0]->empno == $npk) {
                // If approval1_id is the same as the logged in user's NPK,
                // recheck to get empno from the trimmed hirar
                $tempapprov2 = substr($longestHirar, 0, -4); // Trim 4 characters from the longestHirar
                $approval3Result = DB::connection('mysql2')->select(DB::raw(
                    "
                    SELECT empno
                    FROM hirarki
                    WHERE hirar = '$tempapprov2'
                    GROUP BY empno
                    ORDER BY MAX(mutdt) DESC
                    LIMIT 1
                    "
                ));

                // Periksa apakah ada hasil yang ditemukan
                if (!empty($approval3Result)) {
                    // Ambil nilai empno dari objek pertama dalam hasil
                    $approval1 = $approval3Result[0]->empno;
                    // Setelah memastikan $approval1 adalah string, Anda dapat memberikannya ke properti atau variabel yang diharapkan bertipe string
                    $cuti->approval1_id = $approval1;
                } else {
                    // Setel nilai menjadi null atau sesuai kebutuhan jika tidak ada hasil yang ditemukan
                    $cuti->approval1_id = null;
                }
            } else {
                // Jika hasil dari approval3Result bukan NPK yang login, lanjutkan dengan penggunaan empno tersebut
                $approval1 = $approval3Result[0]->empno;
                // Setelah memastikan $approval1 adalah string, Anda dapat memberikannya ke properti atau variabel yang diharapkan bertipe string
                $cuti->approval1_id = $approval1;
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
        $cuti->pjenisizin = $request->input('jenisizin');
        $cuti->note = $request->input('note');
        $cuti->approval_status = '0';
        // dd($approval1);
        if ($request->hasFile('data_verifikasi')) {
        $file = $request->file('data_verifikasi');
        $filename = time() . '_' . $file->getClientOriginalName();
        // Tentukan folder penyimpanan, contohnya 'public/lampiran'
        $path = $file->storeAs('public/lampiran', $filename);

        // Simpan path file ke database
        $cuti->lampiran = $path;
    }
        $cuti->save();
        // Redirect to the index view after successful form submission
        return redirect()->route('cuziaizin.index')->with('success', 'Pengajuan cuti berhasil disimpan.');
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
