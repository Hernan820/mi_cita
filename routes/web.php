<?php

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
/*
Route::get('/', function () {
    return view('welcome');
});
*/



//Route::get('cliente/cita/{idc}', [App\Http\Controllers\DetalleController::class, 'index']);


Route::get('/{idc}', [App\Http\Controllers\DetalleController::class, 'index']);

Route::post('cliente/confirmar/{idcita}', [App\Http\Controllers\DetalleController::class, 'create']);

Route::post('cliente/cancelar/{idcita}', [App\Http\Controllers\DetalleController::class, 'cancelar']);

Route::post('cliente/cupos', [App\Http\Controllers\DetalleController::class, 'cupos']);

Route::post('cita/listarHorario/{id}', [App\Http\Controllers\DetalleController::class, 'listarhoras']);

Route::post('cita/reagendar', [App\Http\Controllers\DetalleController::class, 'reagendar']);

