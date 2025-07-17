<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfGeneratorController;

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




Route::get('/', [PdfGeneratorController::class, 'showEditor'])->name('editor.show');
Route::post('/export-pdf', [PdfGeneratorController::class, 'exportPdf'])->name('pdf.export');

Route::get('/template/load', [PdfGeneratorController::class, 'loadTemplate'])->name('template.load');