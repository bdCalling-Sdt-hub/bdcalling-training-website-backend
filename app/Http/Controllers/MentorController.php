<?php

namespace App\Http\Controllers;

use App\Models\Category;
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


 //get all mentor without any token
 public function allMentors(){

    $allMentor=User::where("userType","MENTOR")->with("category")->get();
    $allMentor = $allMentor?->makeHidden(['verified_email','batchNo','dob','registrationDate','address','bloodGroup','verified_code','category_id']);

    if($allMentor){

     return response()->json([
         "message"=>"All mentors retrived successfully",
         "data"=>$allMentor->shuffle()
        ],200);

    }else{
     return response()->json(['message' => 'Record not found'], 404);
    }


}

public function allMentorsByCategory($catId){
    $user = Auth::guard('api')->user();


    if ($user) {

        if ($user->userType === "SUPER ADMIN") {
            $allMentor=User::where("userType","MENTOR")->where("category_id",$catId)->with(["category"])->get();

            if($allMentor->count()>0){
                return response()->json([
                    "data"=>$allMentor,
                    "message"=>"Data retrived successfully"
                ],200);
            }else{
                return response()->json([

                    "message"=>"Record not found"
                ],404);
            }

        }else {
            return response()->json(['message' => 'You are unauthorized'], 401);
        }
    }else {
        return response()->json(['message' => 'You are unauthorized'], 401);
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

        $mentorData = Mentor::with(["category"])->find($id);
        $mentorData = $mentorData?->makeHidden(['password', 'email_verified_at', 'verified_email', 'approve']);

        if ($mentorData) {

            return response()->json(['message' => 'Mentor data retrive successfully', "data" => $mentorData], 200);
        } else {
            return response()->json(['message' => 'This user not exists'], 404);
        }
    }

//edit specific mentor for super admin



//get all mentor for super admin








}
