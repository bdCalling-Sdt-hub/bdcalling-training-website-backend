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



        $user = User::where('email', $request->email)
            ->where('verified_email', 0)
            ->first();

        if ($user) {
            $otp = $this->generateCode();
            Mail::to($request->email)->send(new DemoMail($otp));
            $user->update(['verified_email' => 0]);
            $user->update(['otp' => $otp]);
            return response(['message' => 'Please check your email for get otp.', 'exists' => true], 200);
        } else {
            Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                return strpos($value, '.') !== false;
            });
            $validator = Validator::make($request->all(),[
                'fullName' => 'required|string|min:2|max:100',
                'email' => 'required|string|email|max:60|unique:users|contains_dot',
                'userName' => 'required|string|max:20|unique:users',
                'verified_email' => 'nullable',
                'password' => 'required|string|min:6|confirmed',
                'userType' => ['required', Rule::in(['STUDENT','MENTOR','SUPER ADMIN','ADMIN'])],
                'otp' => 'nullable',
                'batch_no' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->userType === 'STUDENT';
                    }),
                    'integer', // Add other applicable rules for batch_no
                ],
                'department_name' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->userType === 'STUDENT';
                    }),
                    'string', // Add other applicable rules for department
                ],

                'registration_date' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->userType === 'STUDENT';
                    }),
                    'date', // Add other applicable rules for registration_date
                ],
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
                'userType' => $request->userType,
                'otp' => $this->generateCode(),
                // Add other common fields based on your requirements
            ];



            if ($request->userType === 'STUDENT') {
                $userData['batch_no'] = $request->batch_no;
                $userData['department_name'] = $request->department_name;
                $userData['registration_date'] = $request->registration_date;
                // Add other fields specific to 'STUDENT'
            }

            $user = User::create($userData);

            Mail::to($request->email)->send(new DemoMail($user->otp));
            return response()->json([
                'message' => 'User registration Successfully',
                'user' => $user,
            ]);
        }
    }

    public function emailVerified(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $user = User::where('otp', $request->otp)->first();

        if (!$user) {
            return response(['message' => 'Invalid OTP'], 422);
        }
        $user->update(['verified_email' => 1]);
        $user->update(['otp' => 0]);
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
            if (Auth::user()->verified_email == 0) {
                return response()->json(['error' => 'Your email is not verified'], 401);
            } elseif (Auth::user()->approve == 0) {
                return response()->json(['error' => 'Please wait some time to approval by super admin'], 401);
            } else {
                return $this->respondWithToken($token);
            }
        }

        return response()->json(['error' => 'Your credential is wrong'], 401);
    }

    protected function respondWithToken($token)
    {
        $user = Auth::user();
        $user->makeHidden(['otp', 'userType', 'email_verified_at', 'verified_email','approve']);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()
                ->factory()
                ->getTTL(), //hour*seconds
            'user' => $user,
        ]);
    }

    public function generateCode()
    {
        $this->timestamps = false;
        $this->otp = rand(100000, 999999);
        $this->expire_at = now()->addMinute(3);
        return $this->otp;
    }

    public function guard()
    {
        return Auth::guard();
    }

    public function loggedUserData()
    {
        if ($this->guard()->user()) {
            $user = $this->guard()->user();
            $user = $user->makeHidden(['otp', 'userType',"approve", 'email_verified_at', 'verified_email']);
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
        $user = User::where('email', $request->email)->first();
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
            return response()->json(['error' => 'Unauthorized'], 401);
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

    public function approvelByAdmin(Request $request){
          $user = $this->guard()->user();

          $userType = $user->userType ?? null;

        if ($userType=="SUPER ADMIN") {

            $dataFind=User::find($request->id);
            if($dataFind){
              $dataFind->approve=true;
              $dataFind->update();

              if($dataFind->userType=="MENTOR"){
                Mentor::create([
                    "register_id"=>$dataFind->id
                  ]);

              }elseif($dataFind->userType=="STUDENT"){
                Student::create([
                    "register_id"=>$dataFind->id,
                    "batch_no"=>$dataFind->batch_no,
                    "department_name"=>$dataFind->department_name,
                    "registration_date"=>$dataFind->registration_date
                  ]);

              }


              return response()->json([
                "message"=>"User approved successfully"
              ],200);
            }else{
                return response()->json([
                    "message"=>"Data not found"
                  ],404);
            }

        }elseif($userType==null) {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }
    }
}
