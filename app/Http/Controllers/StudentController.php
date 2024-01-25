<?php

namespace App\Http\Controllers;

use App\Mail\DemoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Category;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use App\Models\Orders;
class StudentController extends Controller
{
    //

    //get all mentor without any token
//  public function allStudents(){

//     $allStudent=User::where("userType","STUDENT")->with("category")->get();
//     $allStudent = $allStudent?->makeHidden(['verified_email','verified_code','designation','expert']);

//     if($allStudent){

//      return response()->json([
//          "message"=>"All mentors retrived successfully",
//          "data"=>$allStudent
//         ],200);

//     }else{
//      return response()->json(['message' => 'Record not found'], 404);
//     }

// }


///////////////////////////////////////////////////



//all student list for super admin by category id and batch no
    public function allStudentList(Request $request){
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
                $categoryName = $request->input('category_id');
                $batchNumber = $request->input('batchNo');
                $perPage = $request->input('per_page', 1);

                $studentsQuery = User::where('userType', 'STUDENT');

                if ($categoryName) {
                    $studentsQuery->where('category_id', $categoryName);
                }

                if ($batchNumber) {
                    $studentsQuery->where('batchNo', $batchNumber);
                }

                $students = $studentsQuery->with(['category'])->orderBy("created_at","desc")->paginate($perPage);
                $students->appends([
                    'category_name' => $categoryName,
                    'batch_number' => $batchNumber,
                    'per_page' => $perPage,
                ]);

                // Get the total number of pages
                $totalPages = $students->lastPage();

                // Add pagination information to the response
                $paginationInfo = [
                    "total_pages" => $totalPages,
                    "current_page" => $students->currentPage(),
                    "per_page" => $perPage,
                ];

                // Add pagination links and information to the response
                $paginationLinks = $students->links();

                //$students=$students->makeHidden(['verified_code', 'email_verified_at', 'verified_email', 'approve', 'password']);
                if($students->count()>0){
                return response()->json([
                   "message"=>"All student data retrived successfully",
                   "data"=>$students,
                   "pageinfo"=>$paginationInfo

                ],200);
               }else{
                  return response()->json([
                    "message"=>"Record not found"
                  ],404);
               }


            } else{
                return response()->json(["message"=>"You are unauthorized"],401);
            }
        } else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }

    }




// student add by super admin





// individual student show by super admin access

public function showStudent($id){
    $user = Auth::guard('api')->user();

    if ($user) {
        if ($user->userType === "SUPER ADMIN") {
            $student = User::find($id);
            if($student){

                return response()->json([
                    "user"=>$student
                ],200);
            }else{
                return response()->json([
                    "message"=>"Record not found"
                ],404);
            }


        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }
    }else{
        return response()->json(["message"=>"You are unauthorized"],401);
    }

}


//student buy course

public function getBuyCourseForStudent(){
    $user = Auth::guard('api')->user();
   
    if ($user) {
        if ($user->userType === "STUDENT") {
          
            
            $orderedCourse=Orders::with(["course"])->where("student_id",$user->id)->where("status","Processing")->get();
            return $orderedCourse;
            

        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    } else {
        return response()->json(["message" => "You are unauthorized"], 401);
    }
}


}
