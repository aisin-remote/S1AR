<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CuziaController;
use App\Http\Controllers\CuziaPribadiController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\CuziaIzinController;
use App\Http\Controllers\CuziaCutiController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\PengajuanIzinController;
use App\Http\Controllers\RekapCutiController;
use App\Http\Controllers\RekapIzinController;
use App\Models\PengajuanIzin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Grup rute yang memerlukan otentikasi
Route::middleware(['auth'])->group(function () {
    Route::get('/', [EmployeeController::class, 'indexPerson']);
    Route::get('/dailyattendance', [EmployeeController::class, 'index'])->name('daily.attendance');
    Route::get('/employee/datatables', [EmployeeController::class, 'getData'])->name('employee.datatables');

    // Route::get('/weeklyattendance', [EmployeeController::class, 'indexWeekly'])->name('weekly.attendance');
    // Route::get('/employee/weekly/datatables', [EmployeeController::class, 'getDataWeekly'])->name('employee.weekly.datatables');

    Route::get('/monthlyattendance', [EmployeeController::class, 'getDataMonthly'])->name('employee.monthly.datatables');
    Route::get('/monthlyattendance/{month?}', [EmployeeController::class, 'getDataMonthly'])->name('monthlyattendance');

    // Route::get('/monthlyattendancedepartment', [EmployeeController::class, 'getDataMonthlyDepartment'])->name('department.monthly.datatables');
    // Route::get('/monthlyattendancedepartment/{department?}', [EmployeeController::class, 'getDataMonthlyDepartment'])->name('departmentattendance');

    Route::get('/historyattendance', [EmployeeController::class, 'indexPerson'])->name('person.attendance');
    Route::get('/employee/history/datatables', [EmployeeController::class, 'getDataPerson'])->name('employee.person.datatables');

    Route::get('/holiday', [HolidayController::class, 'index'])->name('master-holiday');
    Route::post('/upload-holiday', [HolidayController::class, 'upload'])->name('upload-holiday');
    Route::get('/holiday/datatables', [HolidayController::class, 'getDataHoliday'])->name('holiday.datatables');

    Route::get('/cuzia', [CuziaController::class, 'index'])->name('cuzia');
    Route::post('/approve', [CuziaController::class, 'approve'])->name('cuzia.approve');
    // Route::get('/cuzia/{month?}', [CuziaController::class, 'getdataCuzia'])->name('cuziafilter');
    Route::get('/dashboard', [CuziaController::class, 'saldocuti'])->name('saldocuti');
    Route::get('/cuzia/datatables', [CuziaController::class, 'getData'])->name('cuzia.datatables');

    Route::get('/rekapcuti', [RekapCutiController::class, 'index'])->name('rekapcuti');
    Route::get('/rekapcuti/datatables', [RekapCutiController::class, 'getData'])->name('rekapcuti.datatables');

    Route::get('/rekapizin', [RekapIzinController::class, 'index'])->name('rekapizin');
    Route::get('/rekapizin/datatables', [RekapIzinController::class, 'getData'])->name('rekapizin.datatables');

    Route::get('/cuziaizin', [CuziaIzinController::class, 'index'])->name('cuziaizin');
    Route::get('/cuziaizin/datatables', [CuziaIzinController::class, 'getData'])->name('cuziaizin.datatables');
    Route::get('/getDetailData', [CuziaIzinController::class, 'getDetailData'])->name('getDetailData');
    Route::resource('cuziaizin', CuziaIzinController::class);

    Route::get('/cuziacuti', [CuziaCutiController::class, 'index'])->name('cuziacuti');
    Route::get('/cuziacuti/datatables', [CuziaCutiController::class, 'getData'])->name('cuziacuti.datatables');
    // Route::post('/cuziacuti/store', [CuziaCutiController::class, 'store'])->name('cuziacuti.store');
    Route::resource('cuziacuti', CuziaCutiController::class);

    Route::get('/izin', [PengajuanIzinController::class, 'index'])->name('izin');
    Route::get('/izin/datatables', [PengajuanIzinController::class, 'getData'])->name('izin.datatables');
    Route::post('/izinapprove', [PengajuanIzinController::class, 'approve'])->name('izin.approve');
    // Route::post('/cuziaizin', [CuziaIzinController::class, 'store'])->name('cuziaizin.store');
    Route::resource('izin', PengajuanIzinController::class);

    Route::get('/test', [EmployeeController::class, 'test']);
});

// Rute otentikasi
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'doLogin'])->name('store.login');
Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'doRegister'])->name('store.register');
Route::get('logout', [AuthController::class, 'logout']);

Route::get('/scheduler', [SchedulerController::class, 'index']);
Route::get('/scheduler1', [SchedulerController::class, 'index1']);
Route::get('/scheduler2', [SchedulerController::class, 'index2']);
// routes/web.php

Route::get('/cuti/input', function () {
    return view('pengajuancuti');
});

Route::get('/ijin/input', function () {
    return view('pengajuanijin');
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// });
