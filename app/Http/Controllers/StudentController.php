<?php

namespace App\Http\Controllers;

use App\Mail\DemoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Models\User;

class StudentController extends Controller
{
    //

    public function addStudent(Request $request){

//        $user = User::where('email',$request->email)->first();
//        if(!$user){
//            $validator = Validator::make($request->all(),[
//                'student_image' => 'nullable',
//                'full_name' => 'required|string|min:2|max:100',
//                'mobile_number' => 'required',
//                'email' => 'required|string|email|max:60|unique:users',
//                'batch_no' => 'required|integer',
//                'registration_date' => 'required',
//                'dob' => 'nullable',
//                'department_name' => 'nullable',
//                'blood_group' => 'nullable',
//                'address' => 'nullable'
//            ]);
//
//            if ($validator->fails()){
//                return response()->json($validator->errors(),400);
//            }
//            //create user
//            $user = new User();
//            $user->fullName = $request->full_name;
//            $user->userName = $request->full_name;
//            $user->email = $request->email;
//            $user->password = 'bdCalling';
//            $user->otp = 0;
//            $user->save();
////            Mail::to($request->email)->send(new DemoMail($user->otp));
//
//            // create student
//            $student = new Student();
//            $student->register_id = $user->id;
//            if($request->file('student_image')){
//                $student->student_image = $this->saveImage($request);
//            }
//            $student->full_name = $request->full_name;
//            $student->mobile_number = $request->mobile_number;
//            $student->email = $request->email;
//            $student->batch_no = $request->batch_no;
//            $student->registration_date = $request->registration_date;
//            $student->dob = $request->dob;
//            $student->department_name = $request->department_name;
//            $student->blood_group = $request->blood_group;
//            $student->address = $request->address;
//            $student->save();
//
//        Mail::to($request->email)->send(new DemoMail($user->password));
//            return response()->json([
//                'message' => 'Student add Successfully',
//                'user' => $student
//            ]);
//        }else{
//            return response()->json([
//                'message' => 'user already exist',
//            ]);
//        }
//
//    }
//    public function saveImage($request){
//        $image = $request->file('student_image');
//        $imageName = rand().'.'.$image->getClientOriginalExtension();
//        $directory = 'adminAsset/student-image/';
//        $imgUrl = $directory.$imageName;
//        $image->move($directory,$imageName);
//        return $imgUrl;
//    }
        $user = User::where('email',$request->email)->first();
        $student = Student::where('email',$request->email)->first();


        if(!$user && !$student){
            $validator = Validator::make($request->all(),[
                'full_name' => 'required',
                'mobile_number' => 'required',
                'email' => 'required|string|email|max:60|unique:students',
                'batch_no' => 'required',
                'registration_date' => 'required',
                'dob' => 'nullable',
                'department_name' => 'string',
                'blood_group' => 'string',
                'address' => 'string',
            ]);
            if ($validator->fails()){
                return response()->json($validator->errors(),400);
            }
            $register = new User();
            $register->fullName = $request->full_name;
            $register->userName = $request->full_name;
            $register->email = $request->email;
            $register->password = 'bdCalling';
            $register->otp = 0;
            $register->userType = 'student';
            $register->save();

            // create student table record
            $student = new Student();
            $student->register_id = $register->id;


            if($request->file('student_image')){
                $student->student_image = $this->saveImage($request);
            }
            $student->full_name = $request->full_name;
            $student->mobile_number = $request->mobile_number;
            $student->email = $request->email;
            $student->batch_no = $request->batch_no;
            $student->registration_date = $request->registration_date;
            $student->dob = $request->dob;
            $student->department_name = $request->department_name;
            $student->blood_group = $request->blood_group;
            $student->address = $request->address;
            $student->save();
            return response()->json([
                'message' => 'Student add Successfully',
                'student' => $student,
            ]);
        }
        else{
            return response()->json([
                'message' => 'user is already exist',
            ]);
        }
    }
    public function saveImage($request){
        $image = $request->file('student_image');
        $imageName = rand().'.'.$image->getClientOriginalExtension();
        $directory = 'adminAsset/student-image/';
        $imgUrl = $directory.$imageName;
        $image->move($directory,$imageName);
        return $imgUrl;
    }
    public function showStudent(){
        $students = Student::all();
        return response()->json($students);
    }


    public function deleteStudent($id){
        $student = User::where('userType','student')->where('id',$id)->first();


//        if($student->student_image){
//            unlink($student->student_image);
//        }
        if ($student){
            $student->delete();
            return response()->json('student deleted successfully', 201);
        }
        else{
            return response()->json('user does not exist');
        }
    }


    public function updateStudent(Request $request, $id){
        $student = Student::find($id);
        if($student){
            if($request->file('student_image')){
                unlink($student->student_image);
            }
            $student->full_name = $request->full_name;
            $student->mobile_number = $request->mobile_number;
            $student->email = $request->email;
            $student->batch_no = $request->batch_no;
            $student->registration_date = $request->registration_date;
            $student->dob = $request->dob;
            $student->department_name = $request->department_name;
            $student->blood_group = $request->blood_group;
            $student->address = $request->address;
            $student->save();
        }
    }


    public function totalStudent(){
        $total_student = count(Student::all());
        return response()->json([
            'total Student' => $total_student,
        ]);
    }
}
