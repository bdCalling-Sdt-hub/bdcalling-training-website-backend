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
                $perPage = $request->input('per_page', 10000000000);

                $studentsQuery = User::where('userType', 'STUDENT');

                if ($categoryName) {
                    $studentsQuery->where('category_id', $categoryName);
                }

                if ($batchNumber) {
                    $studentsQuery->where('batchNo', $batchNumber);
                }

               $students = $studentsQuery->with(['category',"course"])->orderBy("created_at","desc")->paginate($perPage);


                $students->appends([
                    'category_name' => $categoryName,
                    'batch_number' => $batchNumber,
                    'per_page' => $perPage,
                ]);

                //Get the total number of pages
                $totalPages = $students->lastPage();

                // Add pagination information to the response
                $paginationInfo = [
                    "total_pages" => $totalPages,
                    "current_page" => $students->currentPage(),
                    "per_page" => $perPage,
                ];

                // Add pagination links and information to the response
                $paginationLinks = $students->links();

                $students=$students->makeHidden(['verified_code', 'email_verified_at', 'verified_email','password']);
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


public function newAndOldStudentChartData(Request $request){

    $user = Auth::guard('api')->user();

    if ($user) {
        if ($user->userType === "SUPER ADMIN") {
            $runningYear = now()->year;

            $newStudentsByMonth = User::where('userType', 'STUDENT')
                ->whereYear('created_at', $runningYear)
                ->select(DB::raw("MONTH(created_at) as month"), DB::raw("COUNT(*) as student"))
                ->groupBy(DB::raw("MONTH(created_at)"))
                ->get();

            // Map numeric months to month names
            $monthNames = array_map(function ($month) {
                return date('M', mktime(0, 0, 0, $month, 1));
            }, range(1, 12));

            // Fill in missing months with an empty count
            $newStudent = [];
            foreach ($monthNames as $monthName) {
                $exists = $newStudentsByMonth->where('month', array_search($monthName, $monthNames) + 1)->first();
                $newStudent[] = ['month' => $monthName, 'student' => $exists ? $exists->student : ''];
            }

            //////////////////////////

            $oldStudentsByYear = User::where('userType', 'STUDENT')
                ->whereYear('created_at', '<=', $runningYear - 1)
                ->select(DB::raw("YEAR(created_at) as year"), DB::raw("COUNT(*) as student"))
                ->groupBy(DB::raw("YEAR(created_at)"))
                ->get();

            // Fill in missing years with an empty count
            for ($year = $runningYear - 1; $year >= $runningYear - 12; $year--) {
                $exists = $oldStudentsByYear->where('year', $year)->first();
                if (!$exists) {
                    $oldStudentsByYear->push(['year' => $year, 'student' => '']);
                }
            }

            // Sort the result by year
            $oldStudentsByYear = $oldStudentsByYear->sortByDesc('year')->values()->all();



            return response()->json([
                "old"=>$oldStudentsByYear,
                "new"=>$newStudent
            ]);
        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }
    }else{
        return response()->json(["message"=>"You are unauthorized"],401);
    }



}



}
