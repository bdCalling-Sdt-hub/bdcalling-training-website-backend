<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
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


    //department api
    Route::post("/department",[DepartmentController::class,'departmentAdd']);
    Route::get("/department",[DepartmentController::class,'departmentGet']);
    Route::get("/department/{id}",[DepartmentController::class,'departmentById']);
    Route::put("/department/{id}",[DepartmentController::class,'departmentUpdate']);
    //category api
    Route::post('/category',[CategoryController::class,'categoryAdd']);
    Route::get('/category/{id}',[CategoryController::class,'categoryById']);
    Route::put('/category/{id}',[CategoryController::class,'categoryUpdate']);

    //student api
    Route::post('/admin/add-student',[StudentController::class,'addStudent']);
    Route::post('/admin/show-student',[StudentController::class,'showStudent']);
});
