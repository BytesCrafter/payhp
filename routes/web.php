<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\PayslipController;

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

Route::get('/', [ExcelController::class, 'index']);
Route::post('/import', [ExcelController::class, 'importData']);
Route::post('/export', [ExcelController::class, 'exportData']);

Route::get('/payslip', [PayslipController::class, 'check']);
