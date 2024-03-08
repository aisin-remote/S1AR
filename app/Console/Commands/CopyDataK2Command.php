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

class CopyDataK2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:copy2';

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
        require_once 'DateFunctions.php';

        $waktuSekarang = getCurrentDate();

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
            } else {
                DB::connection('mysql2')->table('kehadiran2')
                    ->where('empno', $data2->empno)
                    ->where('schdt', $data2->schdt)
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

        $this->info('Attdly2 copied successfully!');
        return 1;
    }
}
