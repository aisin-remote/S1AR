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

class CopyDataK1Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:copy1';

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
            } else {
                DB::connection('mysql2')->table('kehadiran1')
                    ->where('empno', $data1->empno)
                    ->where('datin', $data1->datin)
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

        $this->info('Attdly1 copied successfully!');
        return 1;
    }
}
