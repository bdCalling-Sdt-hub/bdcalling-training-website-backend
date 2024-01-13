<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mentor;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\DemoMail;
use Illuminate\Validation\Rule;



class AuthController extends Controller
{
    public function register(Request $request)

    {
        Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
            return strpos($value, '.') !== false;
        });

        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:60|unique:users|contains_dot',
            'userName' => 'required|string|max:20|unique:users',
            'verified_email' => 'nullable',
            'mobileNumber' => 'required',
            'password' => 'required|string|min:6|confirmed',
            'userType' => ['required', Rule::in(['SUPER ADMIN', 'ADMIN'])],
        ], [
            'email.contains_dot' => 'without (.) Your email is invalid',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        if ($request->file('image')) {
            $file = $request->file('image');


            $timeStamp = time(); // Current timestamp
            $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
            $file->storeAs('image', $fileName, 'public');

            $filePath = '/storage/image/' . $fileName;
            $fileUrl = $filePath;


            $userData = [
                'fullName' => $request->fullName,
                'userName' => $request->userName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobileNumber' => $request->mobileNumber,
                'userType' => $request->userType,
                'image' => $fileUrl
            ];





            $user = User::create($userData);
            return response()->json([
                'message' => 'User registration Successfully',
                'user' => $user,
            ]);
        }
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

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Your credential is wrong'], 401);
    }






    protected function respondWithToken($token)
    {

        $user = Auth::user();
        $user->makeHidden(['userType', 'email_verified_at', 'verified_email']);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()
                ->factory()
                ->getTTL(), //hour*seconds
            'user' => $user


        ]);
    }



    public function guard()
    {
        return Auth::guard('api');
    }

    public function loggedUserData()
    {
        if ($this->guard()->user()) {
            $user = $this->guard()->user();
            $user = $user->makeHidden(['userType', 'email_verified_at', 'verified_email']);
            return response()->json($user);
        } else {
            return response()->json(['message' => 'You are unauthorized']);
        }
    }

    public function forgetPassword(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 401);
        } else {
            $otp = $this->generateCode();

            Mail::to($request->email)->send(new DemoMail($otp));

            $user->update(['verified_email' => 0]);
            $user->update(['otp' => $otp]);
            return response()->json(['message' => 'Please check your email for get the OTP']);
        }
    }

    public function emailVerifiedForResetPass(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();
        if (!$user) {
            return response()->json(['error' => 'Your otp does not matched'], 401);
        } else {
            $user->update(['verified_email' => 1]);
            $user->update(['otp' => 0]);
            return response()->json(['message' => 'Now your email is verified'], 200);
        }
    }

    public function resetPassword(Request $request)
    {
        $user = Student::where('email', $request->email)->first();
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else {
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json(['message' => 'Password reset successfully'], 200);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = $this->guard()->user();

        if ($user) {
            if ($user->userType === "SUPER ADMIN") {

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


        }else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function logout()
    {
        if ($this->guard()->user()) {
            $this->guard()->logout();
            return response()->json(['message' => 'User Logged Out successfully'], 200);
        } else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }
    }

    // public function approvelByAdmin(Request $request)
    // {
    //     $user = $this->guard()->user();

    //     $userType = $user->userType ?? null;

    //     if ($userType == "SUPER ADMIN") {

    //         $dataFind = User::find($request->id);
    //         if ($dataFind) {
    //             $dataFind->approve = true;
    //             $dataFind->update();

    //             if ($dataFind->userType == "MENTOR") {
    //                 Mentor::create([
    //                     "register_id" => $dataFind->id
    //                 ]);
    //             } elseif ($dataFind->userType == "STUDENT") {
    //                 Student::create([
    //                     "register_id" => $dataFind->id,
    //                     "batch_no" => $dataFind->batch_no,
    //                     "department_name" => $dataFind->department_name,
    //                     "registration_date" => $dataFind->registration_date
    //                 ]);
    //             }


    //             return response()->json([
    //                 "message" => "User approved successfully"
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 "message" => "Data not found"
    //             ], 404);
    //         }
    //     } elseif ($userType == null) {
    //         return response()->json(['message' => 'You are unauthorized user'], 401);
    //     }
    // }


    public function removeOtherdevice(){
        $user=Auth::guard('api')->user();
        Auth::guard('api')->logoutOtherDevices($user->id);
        return response()->json([
            "id"=>$user->id,
            "message"=>"logout successfully from other device"
        ]);
    }
}
