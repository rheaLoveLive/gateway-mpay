<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebiturController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TabunganController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['check.token'])->group(function () {

    // tabungan
    Route::post('tabungan/datanasabah', [TabunganController::class, 'dataNasabah']);
    Route::post('tabungan/ceksaldonasabah', [TabunganController::class, 'cekSaldoNasabah']);
    Route::post('tabungan/carinasabah', [TabunganController::class, 'cariDataNasabah']);
    Route::post('tabungan/insert/mutasi', [TabunganController::class, 'insertMutasiTab']);
    Route::post('tabungan/insert/mutasi/penarikan', [TabunganController::class, 'insertMutasiPenarikanTab']);

    // debitur
    Route::post('debitur/datadebitur', [DebiturController::class, 'dataDebitur']);
    Route::post('debitur/caridebitur', [DebiturController::class, 'cariDataDebitur']);
    Route::post('debitur/cektagihandebitur', [DebiturController::class, 'cekTagihanNasabah']);
    Route::post('debitur/insert/angsuran', [DebiturController::class, 'insertMutasiAngsuran']);

    // laporan
    Route::post('tabungan/laporan/setoran', [TabunganController::class, 'laporanSetoranTab']);
    Route::post('tabungan/total/setoran', [TabunganController::class, 'totalSetoranTab']);
    Route::post('laporan/rekap/transaksi', [LaporanController::class, 'rekapDataTransaksi']);
    Route::post('debitur/laporan/setoran', [LaporanController::class, 'laporanAngsuranKredit']);
    Route::post('total/angsuran/kredit', [LaporanController::class, 'totalAngsuranKredit']);
});
