<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class CourseController extends Controller
{

    public function courseAdd(Request $request){

        if($this->guard()->user()){
            $course=Course::where("courseName",strtolower($request->courseName))->first();

            if($course){
                return response()->json(["message"=>"This Course already exists"],409);
            }else{
                $validator = Validator::make($request->all(),[
                    'courseName' => 'required|string|min:2|max:100',
                    'language' => 'required|string|min:2|max:100',
                    'courseDetails' => 'required|string|min:10|max:200',
                    'startDate'=>'required | date',
                    'courseTimeLength'=>'required',
                    'price'=>'required',
                    'mentorId'=>'required',
                    'maxStudentLength'=>'required',
                    'skillLevel'=>'required',
                    'address'=>'required',
                    'courseThumbnail'=>'required|file|max:3072'
                ]);

                if ($validator->fails()){
                    return response()->json(["errors"=>$validator->errors()],400);
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

                    $result=Course::create([
                        'courseName'=>strtolower($request->courseName),
                        'language'=>$request->language,
                        'courseDetails'=>$request->courseDetails,
                        'startDate'=>$request->startDate,
                        'courseTimeLength'=>$request->courseTimeLength,
                        'price'=>$request->price,
                        'mentorId'=>$request->mentorId,
                        'maxStudentLength'=>$request->maxStudentLength,
                        'skillLevel'=>$request->skillLevel,
                        'address'=>$request->address,
                        'courseThumbnail'=>$fileUrl

                    ]);

                    return response()->json(["message"=>"Course created successfully","path"=>public_path()],200);

                }

            }

        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }


  }

  public function courseDelete($id){

    if($this->guard()->user()){
        $result = Course::destroy($id);

        return response()->json(["message"=>"Data deleted successfully"],200);
    }else{
        return response()->json(["message"=>"You are unauthorized"],401);
    }

  }

  public function showAllCourse(){
    if($this->guard()->user()){
        $course = Course::all();

        return response()->json(["message"=>"Data deleted successfully","data"=>$course],200);

    }else{
        return response()->json(["message"=>"You are unauthorized"],401);
    }
  }


  public function courseUpdate(Request $request,$id){

    if($this->guard()->user()){


        $course = Course::find($id);

    if (!$course) {
        return response()->json(['message' => 'Course not found'], 404);
    }

    $rules=[
        'courseName' => 'required|string|min:2|max:100',
        'language' => 'required|string|min:2|max:100',
        'courseDetails' => 'required|string|min:10|max:200',
        'startDate'=>'required | date',
        'courseTimeLength'=>'required',
        'price'=>'required',
        'mentorId'=>'required',
        'maxStudentLength'=>'required',
        'skillLevel'=>'required',
        'address'=>'required',
        'courseThumbnail'=>'image|mimes:jpeg,png,jpg,gif|max:2048'
    ];

    $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()){
            return response()->json(["errors"=>$validator->errors()],400);
        }

        $course->courseName=$request->courseName;
        $course->language=$request->language;
        $course->courseDetails=$request->courseDetails;
        $course->startDate=$request->startDate;
        $course->courseTimeLength=$request->courseTimeLength;
        $course->price=$request->price;
        $course->mentorId=$request->mentorId;
        $course->maxStudentLength=$request->maxStudentLength;
        $course->skillLevel=$request->skillLevel;
        $course->address=$request->address;


        if ($request->hasFile('courseThumbnail')) {
            $file = $request->file('courseThumbnail');
            $destination='storage/courseimage/'.$course->courseThumbnail;

            if(File::exists($destination)){
                File::delete($destination);
            }

            $timeStamp = time(); // Current timestamp
            $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
            $file->storeAs('courseimage', $fileName, 'public');

            $filePath = 'storage/courseimage/' . $fileName;
            $fileUrl = $filePath;
            $course->courseThumbnail=$fileUrl;


        }

        $course->update();
        return response()->json([
            "message"=>"course updated successfully"
        ],200);

    }else{
        return response()->json(["message"=>"You are unauthorized"],401);
    }

  }





    public function guard()
    {
        return Auth::guard();
    }
}
