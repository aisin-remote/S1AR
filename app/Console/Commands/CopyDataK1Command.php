<?php

namespace App\Console\Commands;

use App\Models\kehadiran1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        set_time_limit(300);

        try {
            DB::beginTransaction();

            // Retrieve all relevant records at once
            $kehadiran1Records = kehadiran1::whereDate('crtdt', $waktuSekarang)
                ->orWhereDate('lupddt', $waktuSekarang)
                ->orderBy('crtdt', 'desc')
                ->get();

            $totalRecords = $kehadiran1Records->count();
            $this->info("Total records retrieved: $totalRecords");

            foreach ($kehadiran1Records as $data1) {
                // Pengecekan apakah data sudah ada di MySQL2
                $record = DB::connection('mysql2')->table('kehadiran1')
                    ->where('empno', $data1->empno)
                    ->where('datin', $data1->datin)
                    ->first();

                // Insert jika tidak ada, atau update jika ada perubahan pada tanggal pembaharuan
                if (!$record || $record->lupddt != $data1->lupddt) {
                    DB::connection('mysql2')->table('kehadiran1')->updateOrInsert(
                        ['empno' => $data1->empno, 'datin' => $data1->datin],
                        [
                            'empno' => $data1->empno,
                            'datin' => $data1->datin,
                            'timin' => $data1->timin,
                            'datot' => $data1->datot,
                            'timot' => $data1->timot,
                            'lupddt' => $data1->lupddt,
                        ]
                    );
                }
            }

            DB::commit();

            // Verification of total records
            $copiedRecordsCount = DB::connection('mysql2')->table('kehadiran1')
                ->whereDate('crtdt', $waktuSekarang)
                ->orWhereDate('lupddt', $waktuSekarang)
                ->count();

            if ($copiedRecordsCount === $totalRecords) {
                $this->info("Data copied successfully: $copiedRecordsCount records.");
                return 1;
            } else {
                $this->error("Mismatch in copied data. Expected: $totalRecords, Copied: $copiedRecordsCount.");
                DB::rollBack();
                return 0;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return 0;
        }
    }
}
