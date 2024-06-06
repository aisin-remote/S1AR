<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\attrn2;
use App\Models\employee;
use App\Models\hirarki;
use App\Models\hirarkiDesc;
use App\Models\kehadiran1;
use App\Models\kehadiran2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SchedulerController extends Controller
{
    public function index()
    {
        Artisan::call('data:copy');

        return response()->json([
            'result' => Artisan::output(),
        ]);

    //     DB::connection('sqlsrv')
    // ->select("SELECT * FROM attdly1 WHERE CAST(crtdt AS DATE) = '2024-05-20' OR CAST(lupddt AS DATE) = '2024-05-20' ORDER BY crtdt DESC");
    }
    public function index1()
    {
        Artisan::call('data:copy1');

        return response()->json([
            'result' => Artisan::output(),
        ]);
    }
    public function index2()
    {
        Artisan::call('data:copy2');

        return response()->json([
            'result' => Artisan::output(),
        ]);
    }
}
