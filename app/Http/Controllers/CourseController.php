<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class CourseController extends Controller
{

    public function courseAdd(Request $request)
    {




        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {


                $course = Course::where("courseName", strtolower($request->courseName))
                ->where("status",$request->status)
                ->first();

                if ($course) {
                    return response()->json(["message" => "This Course already exists"], 409);
                } else {
                    $validator = Validator::make($request->all(), [
                        'category_id' => 'required',
                        'courseName' => 'required|string|min:2|max:100',
                        'language' => 'required|string|min:2|max:100',
                        'courseDetails' => 'required|string|min:10|max:5000',
                        'startDate' => 'required | date',
                        'courseTimeLength' => 'required',
                        'price' => 'required',
                        'mentorId' => 'required',
                        'maxStudentLength' => 'required',
                        'skillLevel' => 'required',
                        'address' => 'required',
                        'courseThumbnail' => 'required|file|max:6072',
                        'status' => 'required',
                        'batch'=>'required',
                        'end_date'=>'required|date',
                        'seat_left'=>'required',


                        'careeropportunities'=>'required',
                        'carriculum'=>'required',
                        'job_position'=>'required',
                        'software'=>'required',
                        'publish'=>'required'
                    ]);

                    if ($validator->fails()) {
                        return response()->json(["errors" => $validator->errors()], 400);
                    }


                    if ($request->file('courseThumbnail')) {
                        $file = $request->file('courseThumbnail');

                        // Store the file in the storage/app/public directory


                        // You can save $filePath to a database field if needed
                        $timeStamp = time(); // Current timestamp
                        $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('courseimage', $fileName, 'public');

                        $filePath = 'storage/courseimage/' . $fileName;
                        $fileUrl = $filePath;

                        $result = Course::create([
                            'category_id' => $request->category_id,
                            'courseName' => strtolower($request->courseName),
                            'language' => $request->language,
                            'courseDetails' => $request->courseDetails,
                            'startDate' => $request->startDate,
                            'courseTimeLength' => $request->courseTimeLength,
                            'price' => $request->price,
                            'mentorId' => json_encode($request->mentorId),
                            'maxStudentLength' => $request->maxStudentLength,
                            'skillLevel' => $request->skillLevel,
                            'address' => $request->address,
                            'courseThumbnail' => $fileUrl,
                            'status' => $request->status,
                            'batch'=>$request->batch,
                            'discount_price'=>$request->discount_price?$request->discount_price:null,
                            'coupon_code'=>$request->coupon_code?$request->coupon_code:null,
                            'coupon_code_price'=>$request->coupon_code_price?$request->coupon_code_price:null,
                            'end_date'=>$request->end_date,
                            'seat_left'=>$request->seat_left,


                            'careeropportunities' => json_encode($request->careeropportunities),
                            'carriculum' => json_encode($request->carriculum),
                            'job_position' => json_encode($request->job_position),
                            'software' => json_encode($request->software),
                            'popular' => $request->popular ? $request->popular:0,
                            'publish' => $request->publish ? $request->publish:0

                        ]);

                        return response()->json(["message" => "Course created successfully","data"=>$result], 200);
                    }
                }
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }

    // public function courseDelete($id)
    // {

    //     if ($this->guard()->user()->userType == "SUPER ADMIN") {

    //         $dataFind = Course::where('id', $id)->get();

    //         if (count($dataFind) == 0) {
    //             return response()->json(["message" => "Data not found"], 404);
    //         } else {
    //             $result = Course::destroy($id);

    //             return response()->json(["message" => "Data deleted successfully"], 200);
    //         }
    //     } else {
    //         return response()->json(["message" => "You are unauthorized"], 401);
    //     }
    // }

    public function showIndividualCourse($id){
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

               $course = Course::find($id);

                if (!$course) {
                    return response()->json(['message' => 'Course not found'], 404);
                }else{


                    $course['mentorId'] = json_decode($course['mentorId'], true);
                    // $course['mentorId'] = explode(',', $course['mentorId']);
                    // $course['mentorId'] = array_map('trim', $course['mentorId']);
    // Convert careeropportunities field to an array
    $course['careeropportunities'] = json_decode($course['careeropportunities'], true);
    $course['carriculum'] = json_decode($course['carriculum'], true);

    // Convert carriculum field to an array
    //$course['carriculum'] = json_decode($course['carriculum'], true);
    // return is_string($course['carriculum']);
    // if (is_string($course['carriculum'])) {
    //     $course['carriculum'] = explode(',', $course['carriculum']);
    //     $course['carriculum'] = array_map('trim', $course['carriculum']);
    // }

    // $course['carriculum'] = explode(',', $course['carriculum']);


    // $course['carriculum'] = array_map('trim', $course['carriculum']);

    // Convert job_position field to an array
    $course['job_position'] = json_decode($course['job_position'], true);

    // Convert software field to an array
    $course['software'] = json_decode($course['software'], true);

    $mentorDetails = User::whereIn('id', $course['mentorId'])->get();
    $categoryDetails=Category::where("id",$course["category_id"])->first();
    // Include mentor details in the response
    $course['mentorDetails'] = $mentorDetails;
    $course['categoryDetails'] = $categoryDetails;
                    return response()->json([
                        "message"=>"Individual course retrived successfully",
                        "data"=>$course
                    ],200);
                }

            }
        }
    }
//for student
    public function showAllCourse(Request $request)
    {
        $status = $request->input('status');
        $category = $request->input('category');
        $perPage = $request->input('per_page', 9);


        $course = Course::where("publish",1);

        if ($status) {
            $course->where('status', $status);
        }

        if ($category) {
            $course->where('category_id', $category);
        }

        $courses = $course->paginate($perPage);

        $courses->transform(function ($course) {
            $course->mentorId = json_decode($course->mentorId, true);
            $course->careeropportunities = json_decode($course->careeropportunities, true);
            $course->carriculum = json_decode($course->carriculum, true);
            $course->job_position = json_decode($course->job_position, true);
            $course->software = json_decode($course->software, true);

            // Fetch mentor information
            $mentorIds = $course->mentorId;
            $mentors = User::whereIn('id', $mentorIds)->get(['id', 'fullName', 'email','image','designation']); // Adjust the columns as needed

            $course->mentors = $mentors;

            return $course;
        });

        $result = count($courses);

        if ($result == 0) {
            return response()->json(["message" => "Data Not found"], 404);
        } else {
            return response()->json(["message" => "Data Retrived successfully", "data" => $courses], 200);
        }
    }

//for super admin

public function showAllCourseForSuperAdmin(Request $request)
{
    $status = $request->input('status');
    $category = $request->input('category');
    $perPage = $request->input('per_page', 5000000);


    $course = Course::whereIn("publish",[0,1]);

    if ($status) {
        $course->where('status', $status);
    }

    if ($category) {
        $course->where('category_id', $category);
    }

    $courses = $course->paginate($perPage);

    $courses->transform(function ($course) {
        $course->mentorId = json_decode($course->mentorId, true);
        $course->careeropportunities = json_decode($course->careeropportunities, true);
        $course->carriculum = json_decode($course->carriculum, true);
        $course->job_position = json_decode($course->job_position, true);
        $course->software = json_decode($course->software, true);

        // Fetch mentor information
        $mentorIds = $course->mentorId;
        $mentors = User::whereIn('id', $mentorIds)->get(['id', 'fullName', 'email','image','designation']); // Adjust the columns as needed

        $course->mentors = $mentors;

        return $course;
    });

    $result = count($courses);

    if ($result == 0) {
        return response()->json(["message" => "Data Not found"], 404);
    } else {
        return response()->json(["message" => "Data Retrived successfully", "data" => $courses], 200);
    }
}































    public function courseUpdate(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $course = Course::find($id);

                if (!$course) {
                    return response()->json(['message' => 'Course not found'], 404);
                }

                $rules = [
                    'category_id' => 'required',
                    'courseName' => 'required|string|min:2|max:100',
                    'language' => 'required|string|min:2|max:100',
                    'courseDetails' => 'required|string|min:10|max:3000',
                    'startDate' => 'required | date',
                    'courseTimeLength' => 'required',
                    'price' => 'required',
                    'mentorId' => 'required',
                    'maxStudentLength' => 'required',
                    'skillLevel' => 'required',
                    'address' => 'required',
                    'courseThumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                    'status' => 'required',
                    'batch'=>'required',
                    'end_date'=>'required',
                    'seat_left'=>'required',


                    'careeropportunities'=>'required|array',
                    'carriculum'=>'required|array',
                    'job_position'=>'required|array',
                    'software'=>'required|array',
                    'publish'=>'required'


                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json(["errors" => $validator->errors()], 400);
                }
                $course->category_id = $request->category_id;
                $course->courseName = $request->courseName;
                $course->language = $request->language;
                $course->courseDetails = $request->courseDetails;
                $course->startDate = $request->startDate;
                $course->courseTimeLength = $request->courseTimeLength;
                $course->price = $request->price;
                $course->mentorId = $request->mentorId;
                $course->maxStudentLength = $request->maxStudentLength;
                $course->skillLevel = $request->skillLevel;
                $course->address = $request->address;
                $course->status = $request->status;
                $course->batch=$request->batch;
                $course->discount_price=$request->discount_price?$request->discount_price: $course->discount_price;
                $course->coupon_code=$request->coupon_code?$request->coupon_code:$course->coupon_code;
                $course->coupon_code_price=$request->coupon_code_price?$request->coupon_code_price:$course->coupon_code_price;
                $course->end_date=$request->end_date;
                $course->seat_left=$request->seat_left;



                $course->careeropportunities =$request->careeropportunities?json_encode($request->careeropportunities):json_encode($course->careeropportunities);
                $course->carriculum =$request->carriculum?json_encode($request->carriculum):json_encode($course->carriculum);
                $course->job_position =$request->job_position?json_encode($request->job_position):json_encode($course->job_position);
                $course->software =$request->software?json_encode($request->software):json_encode($course->software);
                $course->popular=$request->popular?$request->popular:$course->popular;
                $course->publish=$request->publish?$request->publish: $course->publish;


                if ($request->hasFile('courseThumbnail')) {
                    $file = $request->file('courseThumbnail');
                    $destination = 'storage/courseimage/' . $course->courseThumbnail;

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('courseimage', $fileName, 'public');

                    $filePath = 'storage/courseimage/' . $fileName;
                    $fileUrl = $filePath;
                    $course->courseThumbnail = $fileUrl;
                }

                $course->update();
                return response()->json([
                    "message" => "course updated successfully"
                ], 200);
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }


    public function deleteCourse($courseId)
    {

        $user = Auth::guard('api')->user();

        if ($user) {
            if ($user->userType === "SUPER ADMIN") {

                $course = Course::find($courseId);
                if ($course) {
                    $course->delete();

                    return response()->json([
                        "data" => "Course deleted successfully"
                    ]);
                    // Related classes will also be deleted due to the onDelete('cascade') constraint
                } else {
                    return response()->json([
                        "data" => "Course not found"
                    ], 404);
                }
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }




    public function guard()
    {
        return Auth::guard();
    }


}
