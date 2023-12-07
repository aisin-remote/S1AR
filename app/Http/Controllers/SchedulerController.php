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
    }
}
