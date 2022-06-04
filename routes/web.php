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
Route::get('/download', [ExcelController::class, 'downloadMaster']);
Route::post('/generate', [ExcelController::class, 'bulkGenerate']);
Route::post('/send', [ExcelController::class, 'bulkSend']);

Route::get('/sendmail', [ExcelController::class, 'sendTestMail']);
