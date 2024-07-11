<?php

namespace App\Http\Controllers;

use App\Models\jenisizin;
use App\Models\Pengajuanizin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
// use App\Models\Pengajuanizin;
use App\Models\PengajuanIzin_Document;

class JenisIzinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jenisIzin = JenisIzin::all();
        return view('masterJenisIzin', compact('jenisIzin'));
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

        try {
            // Simpan data jenis izin baru ke dalam database
            $jenisIzin = new JenisIzin();
            $jenisIzin->jenisizin = $request->jenis_izin;
            $jenisIzin->save();

            // Redirect ke halaman index dengan pesan sukses
            return redirect()->route('jenisizin.index')->with('success', 'Data jenis izin berhasil disimpan.');
        } catch (\Exception $e) {
            // Jika terjadi kesalahan, kembalikan ke halaman sebelumnya dengan pesan error
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data jenis izin: ' . $e->getMessage())->withInput();
        }
    }
    public function getDataJenisIzin()
    {
        // Fetch data from jenisizin table
        $jenisizinData = Jenisizin::select(['id', 'jenisizin', 'created_at', 'updated_at'])->get();

        // Use DataTables to format the data
        return DataTables::of($jenisizinData)
            ->make(true);
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
