<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseClass;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    //

   public function addClass(Request $request){
     if($this->guard()->user()->userType=="admin"){
        $validator = Validator::make($request->all(),[
            'course_id'=> 'required',
            'batch' => 'required',
            'module_title' => 'required|string',
            'module_class' => 'required|array',

        ]);

        if ($validator->fails()){
            return response()->json(["errors"=>$validator->errors()],400);
        }


        $course=CourseClass::where("course_id",$request->course_id)
                             ->where("batch",$request->batch)
                             ->get();

        $courseModuleTitle=CourseClass::where("course_id",$request->course_id)
                                        ->where("batch",$request->batch)
                                        ->where("module_title",$request->module_title)
                                        ->get();



        if(count($courseModuleTitle)>0){
            return response()->json([
                "message"=>"This module_title already exists"
            ],409);
        }

        $result=CourseClass::create([
           "course_id"=>$request->course_id,
           "batch"=>$request->batch,
           "module_title"=>$request->module_title,
           "module_no"=>(string)count($course)+1,
           "module_class"=>json_encode($request->module_class)
        ]);

        return response()->json([
            "message"=>"Class added successfully"
        ]);

    }else{
        return response()->json(["message"=>"You are unauthorized"],401);
    }

    }



    public function getAllClassByCourseId($id){

        $result=CourseClass::where("course_id",$id)
        ->orderBy('batch', 'desc') // Order by batch in descending order
        ->first();

        $newBatch=$result->batch;

        $newCourseById=CourseClass::where("course_id",$id)
        ->where('batch', $newBatch) // Order by batch in descending order
        ->get();

        $decodedData = [];

        foreach ($newCourseById as $item) {
            $item['module_class'] = json_decode($item['module_class']); // Decode only the 'module_class' field
            $decodedData[] = $item; // Add the updated item to the new array
        }


        return response()->json([
            "data"=>$decodedData
        ]);

    }


    public function getAllClassByCourseIdAndBatch(Request $request){

        $CourseByIdAndBatch=CourseClass::where("course_id",$request->course_id)
        ->where('batch',$request->batch) // Order by batch in descending order
        ->get();

        $decodedData = [];

        foreach ($CourseByIdAndBatch as $item) {
            $item['module_class'] = json_decode($item['module_class']); // Decode only the 'module_class' field
            $decodedData[] = $item; // Add the updated item to the new array
        }


        return response()->json([
            "data"=>$decodedData
        ]);

    }


 



    public function guard()
    {
        return Auth::guard();
    }
}
