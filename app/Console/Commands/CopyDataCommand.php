<?php

namespace App\Console\Commands;

use App\Models\kehadiran1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $kehadiran1 = Kehadiran1::orderBy('datin', 'desc')->take(10)->get(); // Data yang  masuk berapa nanti tanya HRD

        foreach ($kehadiran1 as $data1) {
            // Pengecekan apakah data sudah ada di MySQL2
            $exists = DB::connection('mysql2')
                ->table('kehadiran1')
                ->where('empno', $data1->empno)
                ->where('datin', $data1->datin)
                ->exists();

            // Jika data belum ada, lakukan insert
            if (!$exists) {
                DB::connection('mysql2')->table('kehadiran1')->insert([
                    'empno' => $data1->empno,
                    'datin' => $data1->datin,
                    'timin' => $data1->timin,
                    'datot' => $data1->datot,
                    'timot' => $data1->timot,
                ]);
            }
        }

        $this->info('Data copied successfully!');
        return 1;
    }
}
