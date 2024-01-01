<?php

namespace App\Http\Controllers;

use App\Mail\DemoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class StudentController extends Controller
{
    //

    public function register(Request $request){



        $user = Student::where('email', $request->email)
            ->where('verified_email', 0)
            ->first();

            if ($user) {

               $token="http://bdcallingacademy.com/verified/";
               $random=Str::random(40);
                Mail::to($request->email)->send(new DemoMail($token.$random));
                $user->update(['verified_code'=>$random]);
                $user->update(['verified_email' => 0]);

                return response(['message' => 'Please check your email for valid your email.'], 200);
            }else{
                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(),[
                    'fullName' => 'required|string|min:2|max:100',
                    'userName' => 'required|string|max:20|unique:students',
                    'email' => 'required|string|email|max:60|unique:students|contains_dot',
                    'password' => 'required|string|min:6|confirmed',
                    'batchNo'=>'required',
                    'departmentName'=>'required',
                    'registrationDate'=>'required',
                    'verified_email' => 'nullable',
                   ],[
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()){
                    return response()->json($validator->errors(),400);
                }

                $userData = [
                    'fullName' => $request->fullName,
                    'userName' => $request->userName,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'batchNo'=>$request->batchNo,
                    'registrationDate'=>$request->registrationDate,
                    'departmentName'=>$request->departmentName,
                    'verified_code'=>Str::random(40)
                 ];

                    $user = Student::create($userData);
                    $token="http://bdcallingacademy.com/verified/";
                    Mail::to($request->email)->send(new DemoMail($token.$user->verified_code));
                    return response()->json([
                        'message' => 'Please check your email to valid your email',

                    ]);



            }

    }


    public function emailVerified(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'verified_code' => 'required',
    ]);

    if ($validator->fails()) {
        return response(['errors' => $validator->errors()], 422);
    }

        $user = Student::where('verified_code', $request->verified_code)->first();

        if (!$user) {
            return response(['message' => 'Invalid'], 422);
        }
        $user->update(['verified_email' => 1]);
        $user->update(['verified_code' => 0]);
        return response(['message' => 'Email verified successfully']);


    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            if ($this->guard()->user()->verified_email == 0) {
                return response()->json(['error' => 'Your email is not verified'], 401);
            } elseif ($this->guard()->user()->approve == 0) {
                return response()->json(['error' => 'Please wait some time to approval by super admin'], 401);
            } else {
                return $this->respondWithToken($token);
            }
        }

        return response()->json(['error' => 'Your credential is wrong'], 401);
    }


    public function loggedUserData()
    {
        if ($this->guard()->user()) {
            $user = $this->guard()->user();
            $user = $user->makeHidden(["password","approve", 'email_verified_at', 'verified_email',"verified_code"]);
            return response()->json($user);
        } else {
            return response()->json(['message' => 'You are unauthorized']);
        }
    }


    public function forgetPassword(Request $request)
    {
        $email = $request->email;
        $user = Student::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 401);
        } else {
               $token="http://bdcallingacademy.com/verified/";
               $random=Str::random(40);

            Mail::to($request->email)->send(new DemoMail($token.$random));

            $user->update(['verified_email' => 0]);
            $user->update(['verified_code' => $random]);
            return response()->json(['message' => 'Please check your email for get the OTP']);
        }
    }


    public function emailVerifiedForResetPass(Request $request)
    {
        $user = Student::where('email', $request->email)
            ->where('verified_code', $request->verified_code)
            ->first();
        if (!$user) {
            return response()->json(['error' => 'Your verified code does not matched'], 401);
        } else {
            $user->update(['verified_email' => 1]);
            $user->update(['verified_code' => 0]);
            return response()->json(['message' => 'Now your email is verified'], 200);
        }
    }





    public function resetPassword(Request $request)
    {
        $user = Student::where('email', $request->email)->first();
        if($user){
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            } else {
                $user->update(['password' => Hash::make($request->password)]);
                return response()->json(['message' => 'Password reset successfully'], 200);
            }
        }else{
            return response()->json(['message' => 'Your email does not exists'], 200);
        }

    }


    public function updatePassword(Request $request)
    {
        $user = $this->guard()->user();
        if ($user) {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 409);
            }
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Your current password is wrong'], 409);
            }
            $user->update(['password' => Hash::make($request->new_password)]);

            return response(['message' => 'Password updated successfully'], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }







    protected function respondWithToken($token)
    {
        $user = $this->guard()->user();
        $user->makeHidden(['verified_code','email_verified_at', 'verified_email','approve','password']);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()
                ->factory()
                ->getTTL(), //hour*seconds
            'user' => $user,
        ]);
    }


    public function guard()
    {
        return Auth::guard('student_api');
    }















//     public function saveImage($request){
//         $image = $request->file('student_image');
//         $imageName = rand().'.'.$image->getClientOriginalExtension();
//         $directory = 'adminAsset/student-image/';
//         $imgUrl = $directory.$imageName;
//         $image->move($directory,$imageName);
//         return $imgUrl;
//     }
//     public function showStudent(){
//         $students = Student::all();
//         return response()->json($students);
//     }


//     public function deleteStudent($id){
//         $student = User::where('userType','student')->where('id',$id)->first();


// //        if($student->student_image){
// //            unlink($student->student_image);
// //        }
//         if ($student){
//             $student->delete();
//             return response()->json('student deleted successfully', 201);
//         }
//         else{
//             return response()->json('user does not exist');
//         }
//     }


//     public function updateStudent(Request $request, $id){
//         $student = Student::find($id);
//         if($student){
//             if($request->file('student_image')){
//                 unlink($student->student_image);
//             }
//             $student->full_name = $request->full_name;
//             $student->mobile_number = $request->mobile_number;
//             $student->email = $request->email;
//             $student->batch_no = $request->batch_no;
//             $student->registration_date = $request->registration_date;
//             $student->dob = $request->dob;
//             $student->department_name = $request->department_name;
//             $student->blood_group = $request->blood_group;
//             $student->address = $request->address;
//             $student->save();
//         }
//     }


//     public function totalStudent(){
//         $total_student = count(Student::all());
//         return response()->json([
//             'total Student' => $total_student,
//         ]);
//     }
}
