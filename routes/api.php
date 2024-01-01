<?php

use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ClassController;



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

    ['middleware' => 'auth:student_api']


], function ($router) {

    Route::post("/student-register",[StudentController::class,"register"]);
    Route::post("/student-verified-email",[StudentController::class,"emailVerified"]);
    Route::post("/student-login",[StudentController::class,"login"]);
    Route::get('/student-profile',[StudentController::class,'loggedUserData']);
    Route::post('/student-forget-pass',[StudentController::class,'forgetPassword']);
    Route::post('/student-verified-checker',[StudentController::class,'emailVerifiedForResetPass']);
    Route::post('/student-reset-password',[StudentController::class,'resetPassword']);
    Route::post('/student-update-pass',[StudentController::class,'updatePassword']);
});


Route::group([

    ['middleware' => 'auth:mentor_api']


], function ($router) {

    Route::post("/mentor-register",[MentorController::class,"register"]);

});




// Route::group([

//     'middleware' => 'api',


// ], function ($router) {

//     Route::post('/register',[AuthController::class,"register"]);
//     Route::post('/verified-email',[AuthController::class,'emailVerified']);
//     Route::post('/login',[AuthController::class,'login']);
//     Route::get('/profile',[AuthController::class,'loggedUserData']);
//     Route::post('/forget-pass',[AuthController::class,'forgetPassword']);
//     Route::post('/otp-checker',[AuthController::class,'emailVerifiedForResetPass']);
//     Route::post('/reset-pass',[AuthController::class,'resetPassword']);
//     Route::post('/update-pass',[AuthController::class,'updatePassword']);
//     Route::get('/logout',[AuthController::class,'logout']);
//     Route::post('/approvel',[AuthController::class,'approvelByAdmin']);



//     //student




//     //department api
//     Route::post("/department",[DepartmentController::class,'departmentAdd']);
//     Route::get("/department",[DepartmentController::class,'departmentGet']);
//     Route::get("/department/{id}",[DepartmentController::class,'departmentById']);
//     Route::put("/department/{id}",[DepartmentController::class,'departmentUpdate']);
//     //category api
//     Route::post('/category',[CategoryController::class,'categoryAdd']);
//     Route::get('/category/{id}',[CategoryController::class,'categoryById']);
//     Route::put('/category/{id}',[CategoryController::class,'categoryUpdate']);


//     //course api

//     Route::post("/course",[CourseController::class,'courseAdd']);
//     Route::delete("/course/{id}",[CourseController::class,'courseDelete']);
//     Route::get("/course",[CourseController::class,'showAllCourse']);
//     Route::put("/course/{id}",[CourseController::class,'courseUpdate']);
//     Route::delete("/course/{courseId}",[CourseController::class,'courseDelete']);

//     //class api

//     Route::post("/class",[ClassController::class,'addClass']);
//     Route::get("/class/{id}",[ClassController::class,'getAllClassByCourseId']);
//     Route::get("/class",[ClassController::class,'getAllClassByCourseIdAndBatch']);
//     Route::get("/class-single/{classid}",[ClassController::class,'showClass']);
//     Route::put("/class/{classid}",[ClassController::class,'editClass']);
// });


