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
use App\Http\Controllers\ClassScheduleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\PaymentSslcommerzeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SeminarController;
use App\Http\Controllers\StudentJourneyController;
use App\Models\StudentJourney;
use Illuminate\Support\Facades\Auth;

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



// Route::group([

//     ['middleware' => 'auth:student_api']


// ], function ($router) {

//     Route::post("/students/register", [StudentController::class, "register"]);
//     Route::post("/students/verified-email", [StudentController::class, "emailVerified"]);
//     Route::post("/students/login", [StudentController::class, "login"]);
//     Route::get('/students/profile', [StudentController::class, 'loggedUserData']);
//     Route::post('/students/forget-pass', [StudentController::class, 'forgetPassword']);
//     Route::post('/students/verified-checker', [StudentController::class, 'emailVerifiedForResetPass']);
//     Route::post('/students/reset-password', [StudentController::class, 'resetPassword']);
//     Route::post('/students/update-pass', [StudentController::class, 'updatePassword']);
// });


// Route::group([

//     ['middleware' => 'auth:mentor_api']


// ], function ($router) {

//     Route::post("/mentors/login", [MentorController::class, "login"]);
//     Route::get("/mentors/profile", [MentorController::class, "loggedUserData"]);
// });


Route::group([

    ['middleware' => 'auth:api']


], function ($router) {

    //authenticatin api

    Route::post("/register", [AuthController::class, "register"]);
    Route::post("/verified-email", [AuthController::class, "emailVerified"]);
    Route::post("/login", [AuthController::class, "login"]);
    Route::get("/profile", [AuthController::class, "loggedUserData"]);
    Route::post('forget-pass', [AuthController::class, 'forgetPassword']);
    Route::post('/verified-checker', [AuthController::class, 'emailVerifiedForResetPass']);
    Route::post('/reset-pass', [AuthController::class, 'resetPassword']);
    Route::post('/update-pass', [AuthController::class, 'updatePassword']);
    Route::put("/profile/edit/{id}", [AuthController::class, 'editProfile']);
    Route::delete("/profile/delete/{id}", [AuthController::class, "deleteProfile"]);

    Route::get("/account/approve/{id}", [AuthController::class, 'accountApproveByAdmin']);
    Route::get("/account/unapprove/{id}", [AuthController::class, 'accountUnapproveByAdmin']);

    //mentors data api

    Route::get("/mentors/all", [MentorController::class, 'allMentors']);
    Route::get("/mentors/all/{catId}", [MentorController::class, "allMentorsByCategory"]);



    //get all students
    Route::get("/students/all", [StudentController::class, "allStudentList"]);






    //department api route
    Route::post("/department", [DepartmentController::class, 'departmentAdd']);
    Route::get("/department", [DepartmentController::class, 'departmentGet']);
    Route::get("/department/{id}", [DepartmentController::class, 'departmentById']);
    Route::put("/department/{id}", [DepartmentController::class, 'departmentUpdate']);

    //category api route

    Route::post('/category', [CategoryController::class, 'categoryAdd']);
    Route::get('/category/{id}', [CategoryController::class, 'categoryById']);
    Route::put('/category/{id}', [CategoryController::class, 'categoryUpdate']);
    Route::get('/category', [CategoryController::class, 'getAllCategory']);

    ////////////////////////////////
    //events

    Route::resource('events', EventController::class);
    Route::resource('galleries', GalleryController::class);
    Route::resource('journies', StudentJourneyController::class);
    Route::resource('schedules', ClassScheduleController::class);
    Route::post("/schedules/department/batch", [ClassScheduleController::class, "scheduleShowByCatAndBatch"]);



    //course api route

    Route::post("/course", [CourseController::class, 'courseAdd']);
    Route::get("/course", [CourseController::class, 'showAllCourse']);
    Route::put("/course/{id}", [CourseController::class, 'courseUpdate']);
    Route::delete("/course/{courseId}", [CourseController::class, 'deleteCourse']);


    //class api

    Route::post("/class", [ClassController::class, 'addClass']);
    Route::get("/class/{id}", [ClassController::class, 'getAllClassByCourseId']);
    Route::get("/class", [ClassController::class, 'getAllClassByCourseIdAndBatch']);
    Route::get("/class-single/{classid}", [ClassController::class, 'showClass']);
    Route::put("/class/{classid}", [ClassController::class, 'editClass']);


    //review api

    Route::resource('reviews', ReviewController::class);


    //sslcommerze payment route
    Route::post('/pay', [PaymentSslcommerzeController::class, 'index']);
    Route::post('/coupon-discount', [PaymentSslcommerzeController::class, 'discountCouponCode']);
    Route::post('/success', [PaymentSslcommerzeController::class, 'success']);
    Route::post('/fail', [PaymentSslcommerzeController::class, 'fail']);
    Route::post('/cancel', [PaymentSslcommerzeController::class, 'cancel']);
    Route::post('/ipn', [PaymentSslcommerzeController::class, 'ipn']);

    //bkash payment route

    Route::get('/bkash/payment', [App\Http\Controllers\BkashTokenizePaymentController::class,'index']);
    Route::get('/bkash/create-payment', [App\Http\Controllers\BkashTokenizePaymentController::class,'createPayment'])->name('bkash-create-payment');
    Route::get('/bkash/callback', [App\Http\Controllers\BkashTokenizePaymentController::class,'callBack'])->name('bkash-callBack');


    // Route::post("/admins/register", [AuthController::class, "register"]);
    // Route::post("/admins/login", [AuthController::class, "login"]);
    // Route::get("/admins/profile", [AuthController::class, "loggedUserData"]);
    // Route::post('/admins/update-pass',[AuthController::class,'updatePassword']);




    // //

    // Route::post("/mentors/register", [MentorController::class, "register"]);
    // Route::post("/mentors/approve/{id}", [MentorController::class, "mentorAccountApproved"]);
    // Route::get("/mentors/profile/{id}", [MentorController::class, "mentorProfileShow"]);
    // Route::delete("/mentors/{id}", [MentorController::class, "mentorAccountDelete"]);
    // Route::put("/mentors/{id}", [MentorController::class, "mentorProfileEdit"]);
    // Route::get("/mentors/all", [MentorController::class, "getAllMentor"]);
    // Route::get("/mentors/all/{catId}",[MentorController::class, "allMentorsByCategory"]);


    // //events

    // Route::resource('events', EventController::class);
    // Route::resource('galleries', GalleryController::class);
    // Route::resource('journies', StudentJourneyController::class);
    // Route::resource('schedules', ClassScheduleController::class);
    // Route::post("/schedules/department/batch",[ClassScheduleController::class,"scheduleShowByCatAndBatch"]);






    // //course api route

    // Route::post("/course",[CourseController::class,'courseAdd']);
    // Route::get("/course",[CourseController::class,'showAllCourse']);
    // Route::put("/course/{id}",[CourseController::class,'courseUpdate']);
    // Route::delete("/course/{courseId}",[CourseController::class,'deleteCourse']);


    // //class api

    // Route::post("/class",[ClassController::class,'addClass']);
    // Route::get("/class/{id}",[ClassController::class,'getAllClassByCourseId']);
    // Route::get("/class",[ClassController::class,'getAllClassByCourseIdAndBatch']);
    // Route::get("/class-single/{classid}",[ClassController::class,'showClass']);
    // Route::put("/class/{classid}",[ClassController::class,'editClass']);


    // //student crud api by super admin
    // Route::get("/admins/students/approve/{id}",[StudentController::class,'accountApproveByAdmin']);
    // Route::get("/admins/students/unapprove/{id}",[StudentController::class,'accountUnapproveByAdmin']);

    // Route::get("admins/students/all",[StudentController::class,"allStudentList"]);

    // Route::post("/admins/students/add",[StudentController::class,"addStudent"]);
    // Route::delete("/admins/students/delete/{id}",[StudentController::class,"deleteStudent"]);
    // Route::get("/admins/students/show/{id}",[StudentController::class,"showStudent"]);
    // Route::put("/admins/students/update/{id}",[StudentController::class,"updateStudent"]);




});


Route::resource('seminers', SeminarController::class);
Route::resource('contacts', ContactController::class);




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
