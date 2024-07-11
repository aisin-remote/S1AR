<?php

namespace App\Console\Commands;

use App\Models\pengajuancutikar;
use App\Models\saldocuti;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CopyDataPengajuanCutiKarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:copyPengajuanCutiKar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy data from SQL Server to MySQL for pengajuancutikar table';

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
            $pengajuancutikarRecords = saldocuti::whereDate('crtdt', $waktuSekarang)
                ->orWhereDate('lupddt', $waktuSekarang)
                ->orderBy('crtdt', 'desc')
                ->get();

            $totalRecords = $pengajuancutikarRecords->count();
            $this->info("Total records retrieved: $totalRecords");

            $dataToInsert = [];

            foreach ($pengajuancutikarRecords as $data) {
                $record = DB::connection('mysql2')->table('pengajuancutikar')
                    ->where('empno', $data->empno)
                    ->first();

                if (!$record) {
                    $dataToInsert[] = [
                        'coid' => $data->coid,
                        'createid' => $data->createid,
                        'Createdt' => $data->Createdt,
                        'empno' => $data->empno,
                        'clget' => $data->clget,
                        'clrig' => $data->clrig,
                        'schdt' => $data->schdt,
                        'tcuti' => $data->tcuti,
                        'stat' => $data->stat,
                        'flag' => $data->flag,
                        'rsccd' => $data->rsccd,
                        'strdt' => $data->strdt,
                        'enddt' => $data->enddt,
                        'expdt' => $data->expdt,
                        'actexp' => $data->actexp,
                        'clmin' => $data->clmin,
                        'clexp' => $data->clexp,
                        'flrig' => $data->flrig,
                        'clsold' => $data->clsold,
                        'clblock' => $data->clblock,
                        'cusrid' => $data->cusrid,
                        'crtdt' => $data->crtdt,
                        'lusrid' => $data->lusrid,
                        'lupddt' => $data->lupddt,
                        'note' => $data->note,
                    ];
                } else {
                    DB::connection('mysql2')->table('pengajuancutikar')
                        ->where('empno', $data->empno)
                        ->update([
                            'coid' => $data->coid,
                            'createid' => $data->createid,
                            'Createdt' => $data->Createdt,
                            'empno' => $data->empno,
                            'clget' => $data->clget,
                            'clrig' => $data->clrig,
                            'schdt' => $data->schdt,
                            'tcuti' => $data->tcuti,
                            'stat' => $data->stat,
                            'flag' => $data->flag,
                            'rsccd' => $data->rsccd,
                            'strdt' => $data->strdt,
                            'enddt' => $data->enddt,
                            'expdt' => $data->expdt,
                            'actexp' => $data->actexp,
                            'clmin' => $data->clmin,
                            'clexp' => $data->clexp,
                            'flrig' => $data->flrig,
                            'clsold' => $data->clsold,
                            'clblock' => $data->clblock,
                            'cusrid' => $data->cusrid,
                            'crtdt' => $data->crtdt,
                            'lusrid' => $data->lusrid,
                            'lupddt' => $data->lupddt,
                            'note' => $data->note,
                        ]);
                }
            }

            if (!empty($dataToInsert)) {
                DB::connection('mysql2')->table('pengajuancutikar')->insert($dataToInsert);
            }

            // Verification of total records
            $copiedRecordsCount = DB::connection('mysql2')->table('pengajuancutikar')
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
