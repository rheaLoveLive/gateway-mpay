<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\DepositoController;
use App\Http\Controllers\PembiayaanController;

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

Route::post('lending/getall', [PembiayaanController::class, 'getAllLend']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['check.token'])->group(function () {
    // // anggotaa
    // Route::post('anggota/getall', [AnggotaController::class, 'getAllAnggota']);
    // Route::post('anggota/checkexist', [AnggotaController::class, 'checkAnggota']);
    // Route::post('anggota/getascif', [AnggotaController::class, 'checkAsCif']);
    // // deposito
    // Route::post('deposito/getall', [DepositoController::class, 'getAllDepo']);
    // Route::post('deposito/data/rekening', [DepositoController::class, 'rekeningDepo']);
    // // Pembiayaan
    // Route::post('lending/data/rekening', [PembiayaanController::class, 'rekeningLend']);
    // Route::post('lending/angsuran', [PembiayaanController::class, 'angsuran']);

    // // Tabungan
    // Route::post('tabungan/getall', [TabunganController::class, 'getAllTab']);
    // Route::post('tabungan/data/rekening', [TabunganController::class, 'rekeningTab']);
    // Route::post('tabungan/mutasi', [TabunganController::class, 'mutasiTab']);
    // Route::post('tabungan/mutasi/all', [TabunganController::class, 'getAllMutasi']);
    // Route::post('tabungan/carirekening', [TabunganController::class, 'cariRekening']);
    // Route::post('tabungan/mutasi/create', [TabunganController::class, 'insertMutasiTab']);
    // Route::post('tabungan/historytrans', [TabunganController::class, 'historyTrans']);
    Route::post('tabungan/datanasabah', [TabunganController::class, 'dataNasabah']);
    Route::post('tabungan/ceksaldonasabah', [TabunganController::class, 'cekSaldoNasabah']);
    Route::post('tabungan/carinasabah', [TabunganController::class, 'cariDataNasabah']);
    Route::post('tabungan/insert/mutasi', [TabunganController::class, 'insertMutasiTab']);
    Route::post('tabungan/insert/mutasi/penarikan', [TabunganController::class, 'insertMutasiPenarikanTab']);
    Route::post('tabungan/laporan/setoran', [TabunganController::class, 'laporanSetoranTab']);
    Route::post('tabungan/total/setoran', [TabunganController::class, 'totalSetoranTab']);
});
