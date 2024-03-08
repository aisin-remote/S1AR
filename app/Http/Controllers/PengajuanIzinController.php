<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\pengajuanizin;

class PengajuanIzinController extends Controller
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

        return view('cuziaizin', compact('groupedData','userInfoOccupation', 'userInfoDept'));
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
                        AND kehadiranmu.rsccd IN ('CTH', 'CL')  -- Filter rsccd sesuai dengan nilai 'CTH' atau 'CL'
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
                    AND kehadiranmu.rsccd IN ('IMU', 'SKT','DLU')
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
        $request->validate([
            'npk' => 'required|string',
            'tgl_mulai' => 'required|string',
            'tgl_selesai' => 'required|string',
        ]);

        $pengajuanizin = new PengajuanIzin();
        // Isi kolom-kolom dalam model PengajuanIzin sesuai dengan data yang ingin disimpan
        $pengajuanizin->part_number = $request->input('part_number');
        $pengajuanizin->part_name = $request->input('part_name');
        $pengajuanizin->pcr_number = $request->input('pcr_number');
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
        $pengajuanizin->pcr_number = $request->input('pcr_number');
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
