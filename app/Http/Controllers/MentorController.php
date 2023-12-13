<?php


namespace App\Http\Controllers;


use App\Models\Mentor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class MentorController extends Controller
{
    //
    public function addMentor(Request $request){
//        $user = User::where('first_name',$request->first_name)->first();
//        $student = Mentor::where('email',$request->email)->first();


        $validator = Validator::make($request->all(),[
            'mentor_image' => 'nullable',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'designation' => 'required|string',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        $register = new User();
        $full_name = $request->first_name .' '. $request->last_name;
        $register->fullName = $full_name;
        $register->userName = $this->setUsernameAttribute($request->first_name,$request->last_name);
        $register->email = $register->userName;
        $register->password = 'mentorbdCalling';
        $register->otp = 0;
        $register->userType = 'mentor';
        $register->save();


        // create student table record
        $mentor = new Mentor();
        $mentor->register_id = $register->id;


        $mentor->user_name = $register->userName;
        if($request->file('mentor_image')){
            $mentor->mentor_image = $this->saveImage($request);
        }
        $mentor->first_name = $request->first_name;
        $mentor->last_name = $request->last_name;
        $mentor->designation = $request->designation;
        $mentor->course_name = $request->course_name;
        $mentor->save();


        return response()->json([
            'message' => 'Mentor add Successfully',
            'student' => $mentor,
        ]);
    }
//        else{
//            return response()->json([
//                'message' => 'Mentor is already exist',
//            ]);
//        }
    public function setUsernameAttribute($first_name,$last_name)
    {
        $last_name = strtolower($last_name);
        $username = $first_name[0] . $last_name;


        $i = 0;
        while(User::whereUsername($username)->exists())
        {
            $i++;
            $username = $first_name[0] . $last_name . $i;
        }
        return $username;
    }

    public function saveImage($request){
        $image = $request->file('mentor_image');
        $imageName = rand().'.'.$image->getClientOriginalExtension();
        $directory = 'adminAsset/mentor-image/';
        $imgUrl = $directory.$imageName;
        $image->move($directory,$imageName);
        return $imgUrl;
    }


    public function showMentor(){
        $mentor = Mentor::get()->all();
        if($mentor){
            return response()->json([
                'mentors' => $mentor,
            ]);
        }else{
            return response()->json('No Mentor exist');
        }
    }


    public function singleMentor($id){
        $single_mentor = Mentor::find($id);


        if(!$single_mentor){
            return response()->json([
                'message' => "user doesn't exist",
            ]);
        }
        return $single_mentor;
    }


    public function updateMentor(Request $request,$id){
        $mentor = Mentor::find($id);
        if($mentor){
            if($request->file('mentor_image')){
                if($mentor->mentor_image){
                    unlink($mentor->mentor_image);
                }
                $mentor->mentor_image = $this->saveImage($request);
            }
            $mentor->first_name = $request->first_name;
            $mentor->last_name = $request->last_name;
            $mentor->designation = $request->designation;
            $mentor->course_name = $request->course_name;
            $mentor->save();
            return response()->json('Mentor Update Successfully',200);
        }
        return response()->json('Mentor does not exist',404);
    }
    public function deleteMentor($id){
        $mentor = User::where('userType','mentor')->where('id',$id)->first();


        if($mentor->mentor_image){
            unlink($mentor->mentor_image);
        }
        if ($mentor){
            $mentor->delete();
            return response()->json('mentor deleted successfully', 201);
        }
        else{
            return response()->json('user does not exist');
        }
    }
}


