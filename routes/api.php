<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
Route::group([

    'middleware' => 'api',


], function ($router) {

    Route::post('/register',[AuthController::class,"register"]);
    Route::post('/verified-email',[AuthController::class,'emailVerified']);
    Route::post('/login',[AuthController::class,'login']);
    Route::get('/profile',[AuthController::class,'loggedUserData']);
    Route::post('/forget-pass',[AuthController::class,'forgetPassword']);
    Route::post('/otp-checker',[AuthController::class,'emailVerifiedForResetPass']);
    Route::post('/reset-pass',[AuthController::class,'resetPassword']);
    Route::post('/update-pass',[AuthController::class,'updatePassword']);
    Route::get('/logout',[AuthController::class,'logout']);


});
