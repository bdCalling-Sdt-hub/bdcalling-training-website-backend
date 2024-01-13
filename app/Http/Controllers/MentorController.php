<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class MentorController extends Controller
{
    //

    public function register(Request $request)
    {

        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
                Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                    return strpos($value, '.') !== false;
                });

                $validator = Validator::make($request->all(), [
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'email' => 'required|string|email|max:60|unique:mentors|contains_dot',
                    'password' => 'required|string|min:6|confirmed',
                    'mentor_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
                    'designation' => 'required'



                ], [
                    'email.contains_dot' => 'without (.) Your email is invalid',
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }




                if ($request->file('mentor_image')) {
                    $file = $request->file('mentor_image');


                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('mentorImage', $fileName, 'public');

                    $filePath = '/storage/mentorImage/' . $fileName;
                    $fileUrl = $filePath;

                    $userData = [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'mentor_image' => $fileUrl,
                        'designation' => $request->designation

                    ];

                    $mentor = Mentor::create($userData);

                    return response()->json([
                        'message' => 'Mentor account create successfully',

                    ], 200);
                }
            } else {
                return response()->json(['message' => 'You are unauthorized']);
            }
        } else {

            return response()->json(['message' => 'You are unauthorized']);
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



        if ($token = Auth::guard('mentor_api')->attempt($credentials)) {

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Your credential is wrong'], 401);
    }


    protected function respondWithToken($token)
    {
        $user = Auth::guard('mentor_api')->user();

        if ($user->approve == 0) {
            return response()->json(['message' => 'You are not approved by super admin']);
        } else {
            $user->makeHidden(['userType', 'email_verified_at', 'verified_email']);
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()
                    ->factory()
                    ->getTTL(), //hour*seconds
                'user' => $user,
            ]);
        }
    }



    public function loggedUserData()
    {
        if (Auth::guard('mentor_api')->user()) {
            $user = Auth::guard('mentor_api')->user();
            $user = $user->makeHidden(['userType', 'email_verified_at', 'verified_email']);
            return response()->json($user);
        } else {
            return response()->json(['message' => 'You are unauthorized']);
        }
    }


    public function mentorAccountApproved($id)
    {

        $user = Auth::guard('api')->user();


        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $mentor = Mentor::find($id);

                if ($mentor) {
                    $mentor->approve = 1;
                    $mentor->update();
                    return response()->json(['message' => 'This mentor now approved'], 200);
                } else {
                    return response()->json(['message' => 'This user not exists'], 404);
                }
            } else {
                return response()->json(['message' => 'You are unauthorized'], 401);
            }
        } else {
            return response()->json(['message' => 'You are unauthorized'], 401);
        }
    }

    public function mentorAccountDelete($id)
    {

        $user = Auth::guard('api')->user();


        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $mentorData = Mentor::find($id);

                if ($mentorData) {

                    $mentorData->delete();


                    return response()->json(['message' => 'This mentor deleted successfully'], 200);
                } else {
                    return response()->json(['message' => 'This user not exists'], 404);
                }
            } else {
                return response()->json(['message' => 'You are unauthorized'], 401);
            }
        } else {
            return response()->json(['message' => 'You are unauthorized'], 401);
        }
    }



    public function mentorProfileShow($id)
    {

        $mentorData = Mentor::find($id);
        $mentorData = $mentorData?->makeHidden(['password', 'email_verified_at', 'verified_email', 'approve']);

        if ($mentorData) {

            return response()->json(['message' => 'Mentor data retrive successfully', "data" => $mentorData], 200);
        } else {
            return response()->json(['message' => 'This user not exists'], 404);
        }
    }

    public function mentorProfileEdit(Request $request, $id)
    {

        $mentorData = Mentor::find($id);

        if (!$mentorData) {
            return response()->json(['message' => 'This mentor is not exists'], 404);
        }

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'designation' => 'required',
            'mentor_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $mentorData->first_name = $request->first_name;
        $mentorData->last_name = $request->last_name;
        $mentorData->designation = $request->designation;

        if ($request->hasFile('mentor_image')) {
            $file = $request->file('mentor_image');
            $destination = '/storage/mentorImage/' . $mentorData->mentor_image;

            if (File::exists($destination)) {
                File::delete($destination);
            }

            $timeStamp = time(); // Current timestamp
            $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
            $file->storeAs('mentorImage', $fileName, 'public');

            $filePath = 'storage/mentorImage/' . $fileName;
            $fileUrl = $filePath;
            $mentorData->mentor_image = $fileUrl;
            $mentorData->update();

            return response()->json([
                "message"=>"mentor data updated successfully"
            ],200);
        }
    }

//get all mentor for super admin

 public function getAllMentor(){
        $user = Auth::guard('api')->user();


        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
               $allMentor=Mentor::get();

               return response()->json([
                "message"=>"All mentors retrived successfully",
                "data"=>$allMentor
               ],200);

            }
        }else {
            return response()->json(['message' => 'You are unauthorized'], 401);
        }
    }

    //get all mentor without any token
     public function allMentors(){

               $allMentor=Mentor::get();

               if($allMentor){

                return response()->json([
                    "message"=>"All mentors retrived successfully",
                    "data"=>$allMentor
                   ],200);

               }else{
                return response()->json(['message' => 'Record not found'], 404);
               }


    }

}
