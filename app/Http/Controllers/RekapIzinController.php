<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\pengajuanizin;

class RekapIzinController extends Controller
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

        return view('rekapizin', compact('groupedData','userInfoOccupation', 'userInfoDept'));
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
                jenisizin,
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
                    pz.id,
                    pz.empno,
                    pz.tgl_mulai,
                    pz.tgl_selesai,
                    pz.jenisizin,
                    pz.tgl_pengajuan,
                    pz.approval1_status,
                    pz.approval1_id,
                    pz.approval2_id,
                    pz.approval_status,
                    pz.note,
                    e.empnm,
                    h.hirar,
                    h.mutdt,
                    hd.descr,
                    @row_number := CASE
                        WHEN pz.empno != @empno_prev OR pz.tgl_mulai != @tgl_mulai_prev
                            THEN 1
                            ELSE @row_number + 1
                        END AS RowNum,
                    @empno_prev := pz.empno,
                    @tgl_mulai_prev := pz.tgl_mulai
                FROM pengajuanizin pz
                INNER JOIN employee e ON pz.empno = e.empno
                INNER JOIN (
                    SELECT empno, MAX(mutdt) AS max_mutdt
                    FROM hirarki
                    GROUP BY empno
                ) max_hirarki ON pz.empno = max_hirarki.empno
                INNER JOIN hirarki h ON max_hirarki.empno = h.empno AND max_hirarki.max_mutdt = h.mutdt
                INNER JOIN hirarkidesc hd ON h.hirar = hd.hirar
                WHERE pz.approval_status LIKE 2
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
        //     $data = PengajuanIzin::where('approval_status', '2');
        // }
        return DataTables::of($data)->make(true);
    }

    public function approve(Request $request)
    {
        $npk = auth()->user()->npk;
        $pengajuanCuti = PengajuanIzin::where('id', $request->id)->first();
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


     public function store(Request $request)
    {
        $request->validate([
            'npk' => 'required|string',
            'tgl_mulai' => 'required|string',
            'tgl_selesai' => 'required|string',
        ]);

        $pengajuanizin = new PengajuanIzin();
        // Isi kolom-kolom dalam model PengajuanIzin sesuai dengan data yang ingin disimpan
        $pengajuanizin->part_number = $request->input('part_number');
        $pengajuanizin->part_name = $request->input('part_name');
        $pengajuanizin->pzr_number = $request->input('pzr_number');
        $pengajuanizin->status = $request->input('status');
        $pengajuanizin->PIC = $request->input('PIC');
        $pengajuanizin->content_change = $request->input('content_change');
        $pengajuanizin->progress = $request->input('progress');
        $pengajuanizin->note = $request->input('note');

        // Simpan data ke dalam database
        $pengajuanizin->save();

        return redirect()->route('pengajuanizin.index')->with('success', 'Data PengajuanIzin berhasil disimpan.');
    }

    public function edit($id)
    {
        // Mengambil data PICA berdasarkan ID
        $pengajuanizin = PengajuanIzin::findOrFail($id);

        return view('pengajuanizin.editPengajuanIzin', compact('pengajuanizin'));
    }

    public function update(Request $request, $id)
    {
        $pengajuanizin = PengajuanIzin::findOrFail($id);

        // Isi kolom-kolom dalam model PengajuanIzin sesuai dengan data yang ingin disimpan
        $pengajuanizin->part_number = $request->input('part_number');
        $pengajuanizin->part_name = $request->input('part_name');
        $pengajuanizin->pzr_number = $request->input('pzr_number');
        $pengajuanizin->status = $request->input('status');
        $pengajuanizin->PIC = $request->input('PIC');
        $pengajuanizin->content_change = $request->input('content_change');
        $pengajuanizin->progress = $request->input('progress');
        $pengajuanizin->note = $request->input('note');

        // Simpan data PengajuanIzin yang telah diupdate ke dalam database
        $pengajuanizin->save();

        return redirect()->route('pengajuanizin.index')->with('success', 'Data PengajuanIzin berhasil diubah.');
    }

    public function delete($id)
    {
        try {
            $pengajuanizin = PengajuanIzin::findOrFail($id);
            // Hapus entitas PengajuanIzin setelah file-file terkait dihapus
            $pengajuanizin->delete();

            return redirect()->back()->with('success', 'Item deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus item.');
        }
    }
}
