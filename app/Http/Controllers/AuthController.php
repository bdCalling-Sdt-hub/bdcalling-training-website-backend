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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;



class AuthController extends Controller
{
    public function register(Request $request)

    {




        $user = User::where('email', $request->email)
            ->where('verified_email', 0)
            ->first();

        if ($user) {
              $token=null;
            if($request->signAs=="website"){
                $token = "https://bdcallingacademy.com/verify-email/";
            }else{
                $token = "https://app.bdcallingacademy.com/dashboard/verify-email/";
            }


            $random = Str::random(40);
            Mail::to($request->email)->send(new DemoMail($token . $random));
            $user->update(['verified_code' => $random]);
            $user->update(['verified_email' => 0]);

            return response(['message' => 'Please check your email for validate your email.'], 200);
        } else {
            Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                return strpos($value, '.') !== false;
            });

            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|min:2|max:100',

                'userName' => 'required|string|max:20|unique:users',
                'email' => 'required|string|email|max:60|unique:users|contains_dot',
                'password' => 'required|string|min:6|confirmed',
                'userType' => ['required', Rule::in(['STUDENT', 'MENTOR', 'ADMIN', 'SUPER ADMIN'])],
                'mobileNumber' => 'required',

            ], [
                'email.contains_dot' => 'without (.) Your email is invalid',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $fileUrl = null;

            if ($request->file('image')) {
                $file = $request->file('image');


                $timeStamp = time(); // Current timestamp
                $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                $file->storeAs('image', $fileName, 'public');

                $filePath = 'storage/image/' . $fileName;
                $fileUrl = $filePath;
            }

            $userData = [
                'fullName' => $request->fullName,
                'userName' => $request->userName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobileNumber' => $request->mobileNumber,
                'userType' => $request->userType,

                'verified_code' => Str::random(40),
                'image' => $fileUrl,

                'batchNo' => $request->batchNo ? $request->batchNo : null,
                'course_id' => $request->course_id ? $request->course_id : null,
                'category_id' => $request->category_id ? $request->category_id : null,
                'dob' => $request->dob ? $request->dob : null,
                'registrationDate' => $request->registrationDate ? $request->registrationDate : null,

                'bloodGroup' => $request->bloodGroup ? $request->bloodGroup : null,
                'address' => $request->address ? $request->address : null,
                'designation' => $request->designation ? $request->designation : null,
                'expert' => $request->expert ? $request->expert : null,
                'approve' => $request->approve ? $request->approve : 0
            ];

            $user = User::create($userData);

            $token=null;
            if($request->signAs=="website"){
                $token = "https://bdcallingacademy.com/verify-email/";
            }else{
                $token = "https://app.bdcallingacademy.com/dashboard/verify-email/";
            }


            Mail::to($request->email)->send(new DemoMail($token . $user->verified_code));
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

        $user = User::where('verified_code', $request->verified_code)->first();

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

        $userData = User::where("email", $request->email)->first();
        if ($userData && Hash::check($request->password, $userData->password)) {
            if ($userData->verified_email == 0) {
                return response()->json(['message' => 'Your email is not verified'], 401);
            }
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
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()
                ->factory()
                ->getTTL(), //hour*seconds
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
            if ($user->userType == "STUDENT") {
                $user->makeHidden(['verified_email', 'verified_code']);
                return response()->json([
                    //hour*seconds
                    'user' => $user


                ]);
            } else if ($user->userType == "MENTOR") {
                $user->makeHidden(['verified_email', 'batchNo', 'dob', 'registrationDate', 'address', 'bloodGroup', 'verified_code', 'category_id']);
                return response()->json([

                    'user' => $user


                ]);
            } else {
                $user->makeHidden(['verified_email', 'verified_code', 'batchNo', 'dob', 'registrationDate', 'address', 'expert', 'category_id']);
                return response()->json([

                    'user' => $user


                ]);
            }

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
            $token = "http://192.168.10.3:5000/verified/";
            $random = Str::random(40);
            Mail::to($request->email)->send(new DemoMail($token . $random));
            $user->update(['verified_code' => $random]);
            $user->update(['verified_email' => 0]);
            return response()->json(['message' => 'Please check your email for get the OTP']);
        }
    }

    public function emailVerifiedForResetPass(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('verified_code', $request->verified_code)
            ->first();

        if (!$user) {

            return response()->json(['error' => 'Your verified code does not matched '], 401);
        } else {
            $user->update(['verified_email' => 1]);
            $user->update(['verified_code' => 0]);
            return response()->json(['message' => 'Now your email is verified'], 200);
        }
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "message" => "Your email is not exists"
            ], 401);
        }
        if ($user->verified_email == 0) {
            return response()->json([
                "message" => "Your email is not verified"
            ], 401);
        }
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
            return response()->json(['message' => 'You are not authorized!'], 401);
        }
    }



    public function editProfile(Request $request, $id)
    {


        $user = $this->guard()->user();

        if ($user?->userType == "SUPER ADMIN" || $user?->userType == "MENTOR" || $user?->userType == "STUDENT") {
            $userData = User::find($id);

            if ($user->userType == "SUPER ADMIN" && $userData->userType == "STUDENT") {



                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(), [
                    'fullName' => 'required|string|min:2|max:100',
                    'mobileNumber' => 'required',

                ], [
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }

                $userData = User::find($id);
                $userData->fullName = $request->fullName ? $request->fullName : $userData->fullName;
                $userData->mobileNumber = $request->mobileNumber ? $request->mobileNumber : $userData->mobileNumber;
                $userData->registrationDate = $request->registrationDate ? $request->registrationDate : $userData->registrationDate;
                $userData->course_id = $request->course_id  ? $request->course_id  : $userData->course_id;
                $userData->category_id = $request->category_id ? $request->category_id : $userData->category_id;
                $userData->dob = $request->dob ? $request->dob : $userData->dob;
                $userData->bloodGroup = $request->bloodGroup ? $request->bloodGroup : $userData->bloodGroup;
                $userData->address = $request->address ? $request->address : $userData->address;
                $userData->registrationDate = $request->registrationDate ? $request->registrationDate : $userData->registrationDate;


                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $destination = 'storage/image/' . $userData->image;

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('image', $fileName, 'public');

                    $filePath = 'storage/image/' . $fileName;
                    $fileUrl = $filePath;
                    $userData->image = $fileUrl;
                }

                $userData->update();
                return response()->json([
                    "message" => "Profile updated successfully"
                ]);
            } else if ($user->userType == "SUPER ADMIN" && $userData->userType == "MENTOR") {


                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(), [
                    'fullName' => 'required|string|min:2|max:100',
                    'mobileNumber' => 'required',

                ], [
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }

                $userData = User::find($id);
                $userData->fullName = $request->fullName;
                $userData->mobileNumber = $request->mobileNumber;

                $userData->category_id = $request->category_id ? $request->category_id : $userData->category_id;
                $userData->designation = $request->designation ? $request->designation : $userData->designation;
                $userData->expert = $request->expert ? $request->expert : $userData->expert;
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $destination = 'storage/image/' . $userData->image;

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('image', $fileName, 'public');

                    $filePath = 'storage/image/' . $fileName;
                    $fileUrl = $filePath;
                    $userData->image = $fileUrl;
                }

                $userData->update();
                return response()->json([
                    "message" => "Profile updated successfully"
                ]);
            } else if ($user->userType == "STUDENT" && $userData->userType == "STUDENT") {

                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(), [
                    'fullName' => 'required|string|min:2|max:100',
                    'mobileNumber' => 'required',

                ], [
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }

                $userData = User::find($id);
                $userData->fullName = $request->fullName ? $request->fullName : $userData->fullName;
                $userData->mobileNumber = $request->mobileNumber ? $request->mobileNumber : $userData->mobileNumber;
                $userData->registrationDate = $request->registrationDate ? $request->registrationDate : $userData->registrationDate;
                $userData->course_id = $request->course_id  ? $request->course_id  : $userData->course_id;
                $userData->category_id = $request->category_id ? $request->category_id : $userData->category_id;
                $userData->dob = $request->dob ? $request->dob : $userData->dob;
                $userData->bloodGroup = $request->bloodGroup ? $request->bloodGroup : $userData->bloodGroup;
                $userData->address = $request->address ? $request->address : $userData->address;
                $userData->registrationDate = $request->registrationDate ? $request->registrationDate : $userData->registrationDate;


                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $destination = 'storage/image/' . $userData->image;

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('image', $fileName, 'public');

                    $filePath = 'storage/image/' . $fileName;
                    $fileUrl = $filePath;
                    $userData->image = $fileUrl;
                }

                $userData->update();
                return response()->json([
                    "message" => "Profile updated successfully"
                ]);
            } else if ($user->userType == "MENTOR" && $userData->userType == "MENTOR") {
                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(), [
                    'fullName' => 'required|string|min:2|max:100',
                    'mobileNumber' => 'required',

                ], [
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }

                $userData = User::find($id);
                $userData->fullName = $request->fullName;
                $userData->mobileNumber = $request->mobileNumber;
                $userData->category_id = $request->category_id ? $request->category_id : $userData->category_id;
                $userData->designation = $request->designation ? $request->designation : $userData->designation;
                $userData->expert = $request->expert ? $request->expert : $userData->expert;
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $destination = 'storage/image/' . $userData->image;

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('image', $fileName, 'public');

                    $filePath = 'storage/image/' . $fileName;
                    $fileUrl = $filePath;
                    $userData->image = $fileUrl;
                }

                $userData->update();
                return response()->json([
                    "message" => "Profile updated successfully"
                ]);
            } else if ($user->userType == "SUPER ADMIN" && $userData->userType == "SUPER ADMIN") {
                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(), [
                    'fullName' => 'required|string|min:2|max:100',
                    'mobileNumber' => 'required',

                ], [
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }

                $userData = User::find($id);
                $userData->fullName = $request->fullName;
                $userData->mobileNumber = $request->mobileNumber;
                $userData->designation = $request->designation ? $request->designation : $userData->designation;

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $destination = 'storage/image/' . $userData->image;

                    if (File::exists($destination)) {
                        File::delete($destination);
                    }

                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('image', $fileName, 'public');

                    $filePath = 'storage/image/' . $fileName;
                    $fileUrl = $filePath;
                    $userData->image = $fileUrl;
                }

                $userData->update();
                return response()->json([
                    "message" => "Profile updated successfully"
                ]);
            }
        } else {
            return response()->json([
                "message" => "You are not authorized!"
            ], 401);
        }
    }


    public function deleteProfile($id)
    {
        $user = $this->guard()->user();
        if ($user?->userType == "SUPER ADMIN") {
            $userData = User::find($id);
            $userData->delete();
            return response()->json([
                "message" => "Profile deleted successfully"
            ], 200);
        } else {
            return response()->json([
                "message" => "You are not authorized!"
            ], 401);
        }
    }

    // public function logout()
    // {
    //     if ($this->guard()->user()) {
    //         $this->guard()->logout();
    //         return response()->json(['message' => 'User Logged Out successfully'], 200);
    //     } else {
    //         return response()->json(['message' => 'You are unauthorized user'], 401);
    //     }
    // }

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



    public function accountApproveByAdmin($id)
    {
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $dataFind = User::find($id);
                if ($dataFind) {
                    $dataFind->approve = true;
                    $dataFind->update();
                    return response()->json([
                        "message" => "Account approved successfully"
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Record not found"
                    ], 404);
                }
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }

    public function accountUnapproveByAdmin($id)
    {
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $dataFind = User::find($id);
                if ($dataFind) {
                    $dataFind->approve = false;
                    $dataFind->update();
                    return response()->json([
                        "message" => "Account inactived successfully"
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Record not found"
                    ], 404);
                }
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }
}
