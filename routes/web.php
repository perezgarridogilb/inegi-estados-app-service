<?php

use App\Http\Controllers\Api\V1\MunicipalityController;
use App\Http\Controllers\Api\V1\StateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [StateController::class, 'getStatesData'])->name('states.data');
Route::get('states/show/{id?}', [MunicipalityController::class, 'show'])->name('municipality.show');

