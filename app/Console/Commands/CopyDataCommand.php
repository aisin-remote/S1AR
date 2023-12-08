<?php

namespace App\Console\Commands;

use App\Models\attrn2;
use App\Models\employee;
use App\Models\hirarki;
use App\Models\hirarkiDesc;
use App\Models\kehadiran1;
use App\Models\kehadiran2;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CopyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy data from SQL Server to MySQL';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $waktuSekarang = Carbon::now()->format('Y-m-d');
        $waktuSekarang = '2023-11-22';

        $kehadiran1 = Kehadiran1::whereDate('crtdt', $waktuSekarang)
            ->orWhereDate('lupddt', $waktuSekarang)
            ->orderBy('crtdt', 'desc')
            ->get();

        foreach ($kehadiran1 as $data1) {
            // Pengecekan apakah data sudah ada di MySQL2
            $record = DB::connection('mysql2')->table('kehadiran1')
                ->where('empno', $data1->empno)
                ->where('datin', $data1->datin)
                ->first();

            // Jika data belum ada, lakukan insert
            if (!$record) {
                DB::connection('mysql2')->table('kehadiran1')->insert([
                    'empno' => $data1->empno,
                    'datin' => $data1->datin,
                    'timin' => $data1->timin,
                    'datot' => $data1->datot,
                    'timot' => $data1->timot,
                    'crtdt' => $data1->crtdt,
                    'lupddt' => $data1->lupddt,
                ]);
            } else if ($record->lupddt != $data1->lupddt) {
                DB::connection('mysql2')->table('kehadiran1')
                    ->where('empno', $data1->empno)
                    ->where('crtdt', $data1->crtdt)
                    ->update([
                        'empno' => $data1->empno,
                        'datin' => $data1->datin,
                        'timin' => $data1->timin,
                        'datot' => $data1->datot,
                        'timot' => $data1->timot,
                        'crtdt' => $data1->crtdt,
                        'lupddt' => $data1->lupddt,
                    ]);
            }
        }

        $kehadiran2 = Kehadiran2::whereDate('crtdt', $waktuSekarang)
            ->orWhereDate('lupddt', $waktuSekarang)
            ->orderBy('crtdt', 'desc')
            ->get();

        foreach ($kehadiran2 as $data2) {
            // Pengecekan apakah data sudah ada di MySQL2

            $record = DB::connection('mysql2')->table('kehadiran2')
                ->where('empno', $data2->empno)
                ->where('schdt', $data2->schdt)
                ->first();

            // Jika data belum ada, lakukan insert
            if (!$record) {
                DB::connection('mysql2')->table('kehadiran2')->insert([
                    'coid' => $data2->coid,
                    'empno' => $data2->empno,
                    'schdt' => $data2->schdt,
                    'rsccd' => $data2->rsccd,
                    'crtdt' => $data2->crtdt,
                    'lupddt' => $data2->lupddt,
                ]);
            } else if ($record->lupddt != $data2->lupddt) {
                DB::connection('mysql2')->table('kehadiran2')
                    ->where('empno', $data2->empno)
                    ->where('crtdt', $data2->crtdt)
                    ->update([
                        'coid' => $data2->coid,
                        'empno' => $data2->empno,
                        'schdt' => $data2->schdt,
                        'rsccd' => $data2->rsccd,
                        'crtdt' => $data2->crtdt,
                        'lupddt' => $data2->lupddt,
                    ]);
            }
        }

        $attrn2 = attrn2::whereDate('crtdt', $waktuSekarang)
            ->orWhereDate('lupddt', $waktuSekarang)
            ->orderBy('crtdt', 'desc')
            ->get();

        foreach ($attrn2 as $dataAttrn2) {
            $record = DB::connection('mysql2')->table('kehadiranmu')
                ->where('empno', $dataAttrn2->empno)
                ->where('schdt', $dataAttrn2->schdt)
                ->first();

            // Jika data belum ada, lakukan insert
            if (!$record) {
                DB::connection('mysql2')->table('kehadiranmu')->insert([
                    'coid' => $dataAttrn2->coid,
                    'empno' => $dataAttrn2->empno,
                    'schdt' => $dataAttrn2->schdt,
                    'rsccd' => $dataAttrn2->rsccd,
                    'crtdt' => $dataAttrn2->crtdt,
                    'lupddt' => $dataAttrn2->lupddt,
                ]);
            } else if ($record->lupddt != $dataAttrn2->lupddt) {
                DB::connection('mysql2')->table('kehadiranmu')
                    ->where('empno', $data2->empno)
                    ->where('crtdt', $data2->crtdt)
                    ->update([
                        'coid' => $dataAttrn2->coid,
                        'empno' => $dataAttrn2->empno,
                        'schdt' => $dataAttrn2->schdt,
                        'rsccd' => $dataAttrn2->rsccd,
                        'crtdt' => $dataAttrn2->crtdt,
                        'lupddt' => $dataAttrn2->lupddt,
                    ]);
            }
        }

        $employee = employee::all();

        foreach ($employee as $dataEmpl) {
            $exists = DB::connection('mysql2')
                ->table('employee')
                ->where('empno', $dataEmpl->empno)
                ->exists();

            if (!$exists) {
                DB::connection('mysql2')->table('employee')->insert([
                    'coid' => $dataEmpl->coid,
                    'empno' => $dataEmpl->empno,
                    'empnm' => $dataEmpl->empnm,
                ]);
            }
        }

        $hirarki = hirarki::all();

        foreach ($hirarki as $dataHirar) {
            $exists  = DB::connection('mysql2')
                ->table('hirarki')
                ->where('empno', $dataHirar->empno)
                ->where('mutdt', $dataHirar->mutdt)
                ->exists();

            if (!$exists) {
                DB::connection('mysql2')->table('hirarki')->insert([
                    'empno' => $dataHirar->empno,
                    'hirar' => $dataHirar->hirar,
                    'mutdt' => $dataHirar->mutdt,
                ]);
            }
        }

        $hirarkiDesc = hirarkiDesc::all();

        foreach ($hirarkiDesc as $dataHirarDesc) {
            $exists  = DB::connection('mysql2')
                ->table('hirarkidesc')
                ->where('hirar', $dataHirarDesc->hirar)
                ->exists();

            if (!$exists) {
                DB::connection('mysql2')->table('hirarkidesc')->insert([
                    'hirar' => $dataHirarDesc->hirar,
                    'descr' => $dataHirarDesc->descr,
                ]);
            }
        }

        $this->info('Data copied successfully!');
        return 1;
    }
}
