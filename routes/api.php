<?php

use App\Http\Controllers\Api\V1\EmailController;
use App\Http\Controllers\Api\V1\MunicipalityController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\StateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('states', [StateController::class, 'list'])->name('states.list');
Route::post('states/save', [StateController::class, 'save'])->name('states.save');
Route::post('states/sync', [StateController::class, 'sync'])->name('states.sync');

Route::get('municipality/{cve_ent?}', [MunicipalityController::class, 'list'])->name('municipality.list');
Route::post('municipality/sync/{key_state?}', [MunicipalityController::class, 'sync'])->name('municipality.sync');
Route::post('municipality/save', [MunicipalityController::class, 'save'])->name('municipality.save');
Route::post('municipality/delete/{cve_ent?}', [MunicipalityController::class, 'delete'])->name('municipality.delete');
//  Route::prefix('v1')->middleware('api','jwt.verify')->group(function(){
//     Route::prefix('emails')->group(function(){
//     Route::post('/', [EmailController::class,'sendOrderShippedEmail']);
//     // Route::post('refresh', [AuthController::class,'refresh']);
//     Route::get('me', [AuthController::class,'me']);
// });    
// });


