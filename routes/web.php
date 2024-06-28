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


Route::get('/{vista}/{idc}', [App\Http\Controllers\DetalleController::class, 'index']);

Route::post('cliente/confirmar', [App\Http\Controllers\DetalleController::class, 'confirmar']);

Route::post('cliente/cancelar', [App\Http\Controllers\DetalleController::class, 'cancelar']);

Route::post('cliente/oficinas', [App\Http\Controllers\DetalleController::class, 'oficinas']);

Route::post('cliente/fechasoficinas', [App\Http\Controllers\DetalleController::class, 'fechas']);

Route::post('cita/listarHorario', [App\Http\Controllers\DetalleController::class, 'listarhoras']);

Route::post('cita/reagendar', [App\Http\Controllers\DetalleController::class, 'reagendar']);

Route::post('crear/citafisica', [App\Http\Controllers\DetalleController::class, 'store']);

Route::post('cupos/horario/{id}/{oficina}', [App\Http\Controllers\DetalleController::class, 'horasdecupo']);
