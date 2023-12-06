<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SchedulerController;
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

    Route::get('/test', [EmployeeController::class, 'test']);
});

// Rute otentikasi
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'doLogin'])->name('store.login');
Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'doRegister'])->name('store.register');
Route::get('logout', [AuthController::class, 'logout']);

Route::get('/scheduler', [SchedulerController::class, 'index']);