<?php

namespace App\Console\Commands;

use App\Models\Kehadiran2;
use App\Models\attrn2;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        set_time_limit(300);

        try {
            DB::beginTransaction();

            // Retrieve all relevant records at once
            $kehadiran2Records = Kehadiran2::whereDate('crtdt', $waktuSekarang)
                ->orWhereDate('lupddt', $waktuSekarang)
                ->orderBy('crtdt', 'desc')
                ->get();

            $totalRecords = $kehadiran2Records->count();
            $this->info("Total records retrieved: $totalRecords");

            $dataToInsert = [];

            foreach ($kehadiran2Records as $data2) {
                $record = DB::connection('mysql2')->table('kehadiran2')
                    ->where('empno', $data2->empno)
                    ->where('schdt', $data2->schdt)
                    ->first();

                if (!$record) {
                    $dataToInsert[] = [
                        'coid' => $data2->coid,
                        'empno' => $data2->empno,
                        'schdt' => $data2->schdt,
                        'rsccd' => $data2->rsccd,
                        'crtdt' => $data2->crtdt,
                        'lupddt' => $data2->lupddt,
                    ];
                } else if ($record->lupddt != $data2->lupddt) {
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

            if (!empty($dataToInsert)) {
                DB::connection('mysql2')->table('kehadiran2')->insert($dataToInsert);
            }

            // Verification of total records
            $copiedRecordsCount = DB::connection('mysql2')->table('kehadiran2')
                ->whereDate('crtdt', $waktuSekarang)
                ->orWhereDate('lupddt', $waktuSekarang)
                ->count();

            if ($copiedRecordsCount === $totalRecords) {
                $this->info("Data copied successfully: $copiedRecordsCount records.");
                DB::commit();
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
