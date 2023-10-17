<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EmployeeController;
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
    Route::get('/', [EmployeeController::class, 'index']);
    Route::get('/dailyattendance', [EmployeeController::class, 'index'])->name('daily.attendance');
    
    Route::get('/employee/datatables', [EmployeeController::class, 'getData'])->name('employee.datatables');
});

// Rute otentikasi
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'doLogin'])->name('store.login');
Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'doRegister'])->name('store.register');
Route::get('logout', [AuthController::class, 'logout']);
