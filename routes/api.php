<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ClientsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('products')->group(function(){
    Route::get('/',[ProductsController::class, 'index']);
    Route::post('/access',[ProductsController::class, 'access']);
});

Route::prefix('clients')->group(function(){
    Route::post('/c',[ClientsController::class, 'index']);
});