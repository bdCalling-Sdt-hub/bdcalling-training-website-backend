<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Mentor;
use Illuminate\Support\Facades\Auth;

class MentorController extends Controller
{
    //

    public function register(Request $request){

        Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
            return strpos($value, '.') !== false;
        });

        $validator = Validator::make($request->all(),[
            'first_name' => 'required|string|min:2|max:100',
            'last_name' => 'required|string|max:20|unique:students',
            'email' => 'required|string|email|max:60|unique:mentors|contains_dot',
            'password' => 'required|string|min:6|confirmed',
            'mentor_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
            'designation'=>'required',



           ],[
            'email.contains_dot' => 'without (.) Your email is invalid',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        $userData = [
            'first_name' => $request->fullName,
            'last_name' => $request->userName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mentor_image'=>$request->batchNo,
            'designation'=>$request->registrationDate

         ];



         if ($request->file('mentor_image')) {
            $file = $request->file('mentor_image');


            $timeStamp = time(); // Current timestamp
            $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
            $file->storeAs('mentorImage', $fileName, 'public');

            $filePath = '/storage/mentorImage/' . $fileName;
            $fileUrl = $filePath;


        }

            $user = Student::create($userData);
            $token="http://bdcallingacademy.com/verified/";
            Mail::to($request->email)->send(new DemoMail($token.$user->verified_code));
            return response()->json([
                'message' => 'Please check your email to valid your email',

            ]);


    }
}
