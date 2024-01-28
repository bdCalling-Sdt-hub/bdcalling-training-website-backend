<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseClass;
use App\Models\Mentor;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    //

    public function addClass(Request $request)
    {

        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $validator = Validator::make($request->all(), [
                    'course_id' => 'required',
                    'module_title' => 'required|string',
                    'module_class' => 'required|array',

                ]);

                if ($validator->fails()) {
                    return response()->json(["errors" => $validator->errors()], 400);
                }


                $course = CourseClass::where("course_id", $request->course_id)
                    ->get();

                $courseModuleTitle = CourseClass::where("course_id", $request->course_id)
                    ->where("module_title", strtolower($request->module_title))
                    ->get();



                if (count($courseModuleTitle) > 0) {
                    return response()->json([
                        "message" => "This module_title already exists"
                    ], 409);
                }

                $result = CourseClass::create([
                    "course_id" => $request->course_id,
                    "module_title" => strtolower($request->module_title),
                    "module_no" => (string)count($course) + 1,
                    "module_class" => json_encode($request->module_class)
                ]);

                $course=Course::find($request->course_id);
                $course->publish=1;
                $course->update();

                return response()->json([
                    "message" => "Class added successfully"
                ]);
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }



    public function getAllClassByCourseId($id)
    {

        // $courseClasses = CourseClass::where("course_id", $id)->with("course")->get();

        // $courseClasses->transform(function ($classes) {
        //     $classes['module_class'] = json_decode($classes['module_class'], true);
        //     $classes['course']['careeropportunities'] = json_decode($classes['course']['careeropportunities'], true);
        //     $classes['course']['carriculum'] = json_decode($classes['course']['carriculum'], true);
        //     $classes['course']['job_position'] = json_decode($classes['course']['job_position']);
        //     $classes['course']['software'] = json_decode($classes['course']['software']);

        //     $classes['course']['mentorId'] = json_decode($classes['course']['mentorId'], true);
        //     // Fetch mentor information
        //     $mentorIds =  $classes['course']['mentorId'];
        //     $mentors = User::whereIn('id', $mentorIds)->get(['id', 'fullName', 'email', 'image', 'designation']); // Adjust the columns as needed

        //     $classes->mentors = $mentors;

        //     return $classes;
        // });



        // return response()->json([
        //     "data" => $courseClasses
        // ]);

        $courseClasses = CourseClass::where("course_id", $id)
        ->with("course")
        ->get()
        ->map(function ($item) {
            // Ensure module_class is an array
            $item->module_class = is_array($item->module_class) ? $item->module_class : json_decode($item->module_class, true);

            // Ensure careeropportunities, carriculum, job_position, and software are arrays
            $item->course->careeropportunities = is_array($item->course->careeropportunities) ? $item->course->careeropportunities : json_decode($item->course->careeropportunities, true);
            $item->course->carriculum = is_array($item->course->carriculum) ? $item->course->carriculum : json_decode($item->course->carriculum, true);
            $item->course->job_position = is_array($item->course->job_position) ? $item->course->job_position : json_decode($item->course->job_position, true);
            $item->course->software = is_array($item->course->software) ? $item->course->software : json_decode($item->course->software, true);

            // Get mentor information
            $mentorIds = is_array($item->course->mentorId) ? $item->course->mentorId : json_decode($item->course->mentorId, true);
            $mentors = User::whereIn('id', $mentorIds)->get();
            $item->course->mentors = $mentors;

            return $item;
        });

        return response()->json([
         "data"=>$courseClasses
        ]);


    }


    public function getAllClassByCourseIdAndBatch(Request $request)
    {

        $CourseByIdAndBatch = CourseClass::where("course_id", $request->course_id)
            ->where('batch', $request->batch) // Order by batch in descending order
            ->get();

        $decodedData = [];

        foreach ($CourseByIdAndBatch as $item) {
            $item['module_class'] = json_decode($item['module_class']); // Decode only the 'module_class' field
            $decodedData[] = $item; // Add the updated item to the new array
        }


        return response()->json([
            "data" => $decodedData
        ]);
    }


    public function showClass($classid)
    {

        $classDetails = CourseClass::with(["course"])->where("id", $classid)->first();

        if ($classDetails) {
            $data = json_decode($classDetails["module_class"]);

            $classDetails["module_class"] = $data;

            return response()->json([
                "data" => $classDetails
            ]);
        } else {
            return response()->json([
                "message" => "Don't have any data"
            ], 404);
        }
    }

    public function editClass(Request $request, $classid)
    {

        $user = Auth::guard('api')->user();

        if ($user) {
            if ($user->userType === "SUPER ADMIN") {

                $validator = Validator::make($request->all(), [
                    'course_id' => 'required',
                    'module_title' => 'required|string',
                    'module_class' => 'required|array',

                ]);

                if ($validator->fails()) {
                    return response()->json(["errors" => $validator->errors()], 400);
                }

                $class = CourseClass::with(["course"])->find($classid);
                //return $class;

                if ($class) {
                    $courseModuleTitle = CourseClass::where("course_id", $request->course_id)

                        ->where("module_title", strtolower($request->module_title))
                        ->get();

                        //return count($courseModuleTitle);




                        $class->course_id = $request->course_id;

                        $class->module_title = $request->module_title;
                        $class->module_no = $request->module_no;
                        $class->module_class = json_encode($request->module_class);
                        $class->update();
                        return response()->json([
                            "message" => "Class edit successfully",
                            "data" => $class
                        ], 200);

                } else {
                    return response()->json(["message" => "Class not found"], 404);
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
