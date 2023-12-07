<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\DemoMail;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    public function register(Request $request){

        $user=User::where("email",$request->email)
                    ->where("verified_email",0)
                    ->first();

        if($user){
            $otp=$this->generateCode();
            Mail::to($request->email)->send(new DemoMail($otp));
            $user->update(['verified_email' =>0]);
            $user->update(['otp' =>$otp]);
            return response(['message' => 'Please check your email for get otp.',"exists"=>true],200);
        }else{
            Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                return strpos($value, '.') !== false;
            });


            $validator = Validator::make($request->all(),[
                'fullName' => 'required|string|min:2|max:100',
                'email' => 'required|string|email|max:60|unique:users|contains_dot',
                'userName' => 'required|string|max:20|unique:users',
                'verified_email' => 'nullable',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'nullable',
                'otp' => 'nullable'

            ],[
                'email.contains_dot' => 'without (.) Your email is invalid',
            ]);
            if ($validator->fails()){
                return response()->json($validator->errors(),400);
            }
            $user = User::create([
                'fullName' => $request->fullName,
                'userName' => $request->userName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp' => $this->generateCode()
            ]);
            Mail::to($request->email)->send(new DemoMail($user->otp));
            return response()->json([
                'message' => 'User registration Successfully',
                'user' => $user
            ]);
        }


   }

   public function emailVerified(Request $request){
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
    $user->update(['verified_email'=> 1]);
    $user->update(['otp'=> 0]);
    return response(['message' => 'Email verified successfully']);
}


   public function login(Request $request)
   {

        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        $credentials = $request->only('email', 'password');


        if ($token = $this->guard()->attempt($credentials)){


            if(Auth::user()->verified_email==0){
               return response()->json(['error' => 'Your email is not verified'], 401);
            }elseif (Auth::user()->role=="unknown") {
                return response()->json(['error' => 'Please wait some time to set your role by admin'], 401);
            }else{
                return $this->respondWithToken($token);
            }

        }

        return response()->json(['error' => 'Your credential is wrong'], 401);

    }


    protected function respondWithToken($token){
        $user=Auth::user();
        $user->makeHidden(['otp', 'role',"email_verified_at",'verified_email']);
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()-> getTTL(),//hour*seconds
            'user'=>$user
        ]);
    }

    public function generateCode(){
        $this->timestamps = false;
        $this->otp =rand(100000,999999);
        $this->expire_at = now()->addMinute(3);
        return $this->otp;
    }

    public function guard()
    {
        return Auth::guard();
    }


    public function loggedUserData()
    {
        if($this->guard()->user()){
            $user=$this->guard()->user();
            $user=$user->makeHidden(['otp', 'role',"email_verified_at",'verified_email']);
            return response()->json($user);
        }else{
            return response()->json(["message"=>"You are unauthorized"]);
        }

    }


    public function forgetPassword(Request $request){
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if(!$user){
            return response()->json(['error' => 'Email not found'], 401);
        }else{
            $otp =$this->generateCode();

            Mail::to($request->email)->send(new DemoMail($otp));

            $user->update(['verified_email'=> 0]);
            $user->update(['otp'=> $otp]);
            return response()->json(["message"=>"Please check your email for get the OTP"]);
        }

    }

    public function emailVerifiedForResetPass(Request $request){
        $user = User::where('email', $request->email)
                     ->where('otp', $request->otp)
                     ->first();
        if(!$user){
            return response()->json(['error' => 'Your otp does not matched'], 401);
        }else{
            $user->update(['verified_email'=> 1]);
            $user->update(['otp'=> 0]);
            return response()->json(["message"=>"Now your email is verified"],200);
        }
    }

    public function resetPassword(Request $request){
        $user = User::where('email', $request->email)->first();
        $validator = Validator::make($request->all(),[
           'password' => 'required|string|min:6|confirmed',
         ]);

        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }else{
            $user->update(['password'=> Hash::make($request->password)]);
            return response()->json(["message"=>"Password reset successfully"],200);
        }




    }

    public function updatePassword(Request $request){

        $user=$this->guard()->user();
        if($user){
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 409);
            }
            if(!Hash::check($request->current_password, $user->password)){
                return response()->json(['message' => 'Your current password is wrong'], 409);
            }
            $user->update(['password' => Hash::make($request->new_password)]);

            return response(['message' => 'Password updated successfully'],200);

        }else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }


    }


        public function logout()
        {
            if($this->guard()->user()){
                
                $this->guard()->logout();
                return response()->json(['message'=>'User Logged Out successfully'],200);
            }else{
                return response()->json(['message'=>'You are unauthorized user'],401);
            }

        }
}
