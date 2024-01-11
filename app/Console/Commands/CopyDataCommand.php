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
        require_once 'DateFunctions.php';

        $waktuSekarang = getCurrentDate();

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
