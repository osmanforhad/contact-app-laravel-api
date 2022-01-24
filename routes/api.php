<?php

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix'=>"users",'namespace'=>"Users"], function() {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('contact/add', [ContactController::class, 'addContacts']);
    Route::get('contact/get-all/{token}/{pagination?}', [ContactController::class, 'getPaginatedData']);
    Route::post('contact/update/{id}', [ContactController::class, 'editSingleData']);
    Route::post('contact/delete/{id}', [ContactController::class, 'deleteContacts']);
    Route::get('contact/get-single/{id}', [ContactController::class, 'getSingleData']);
    Route::get('contact/search/{search}/{token}/{pagination?}', [ContactController::class, 'searchData']);
});
