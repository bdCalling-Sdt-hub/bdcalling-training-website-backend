<?php

use App\Http\Controllers\MentorController;
use App\Http\Controllers\StudentController;
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

    'middleware' => 'guest',
//    'prefix' => 'auth',


], function ($router) {

    Route::get('/test',[AuthController::class,'testRoute']);
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/verified-email',[AuthController::class,'emailVerified']);
    Route::post('/login',[AuthController::class,'login']);

    Route::get('/profile',[AuthController::class,'loggedUserData']);
    Route::post('/forget-pass',[AuthController::class,'forgetPassword']);
    Route::post('/otp-checker',[AuthController::class,'emailVerifiedForResetPass']);
    Route::post('/reset-pass',[AuthController::class,'resetPassword']);
    Route::post('/update-pass',[AuthController::class,'updatePassword']);
    Route::get('/logout',[AuthController::class,'logout']);


    // add student
    Route::post('/admin/add-student',[StudentController::class,'addStudent']);
    Route::get('/admin/show-students',[StudentController::class,'showStudent']);
    Route::get('/admin/single-students',[StudentController::class,'showStudent']);
    Route::get('/admin/update-students',[StudentController::class,'updateStudent']);
    Route::get('/admin/delete-students/{id}',[StudentController::class,'deleteStudent']);
    Route::get('/admin/total-students',[StudentController::class,'totalStudent']);

    //mentor api
    Route::post('/admin/add-mentor',[MentorController::class,'addMentor']);
    Route::get('/admin/show-mentor',[MentorController::class,'showMentor']);
    Route::get('/admin/single-mentor/{id}',[MentorController::class,'singleMentor']);
    Route::post('/admin/update-mentor/{id}',[MentorController::class,'updateMentor']);
    Route::post('/admin/delete-mentor/{id}',[MentorController::class,'deleteMentor']);
});
