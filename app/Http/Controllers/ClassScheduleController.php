<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Orders;
class ClassScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //return $request->all();
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
                $validator = Validator::make($request->all(),[
                    'time' => 'required|date_format:H:i|after:08:00|before:19:00',
                    'date' => 'required | date',
                    'category_id' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            // Custom validation rule for foreign key existence
                            $exists = \DB::table('categories')->where('id', $value)->exists();

                            if (!$exists) {
                               // $fail("The selected $attribute is invalid.");
                                $fail("The selected course name is invalid.");
                            }
                        },
                    ],
                    'batch'=>'required',
                    'mentor_id' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            // Custom validation rule for foreign key existence
                            $exists = \DB::table('users')->where('id', $value)->exists();

                            if (!$exists) {
                                $fail("The selected mentor is invalid.");
                            }
                        },
                    ],
                    'course_id'=>'required'

               ]);

                if ($validator->fails()){
                    return response()->json(["errors"=>$validator->errors()],400);
                }



                $existingRecord = ClassSchedule::where([
                    'time' => $request->time,
                    'date' => $request->date,
                    'mentor_id' => $request->mentor_id,
                ])->first();

             if($existingRecord){
                return response()->json(["message"=>"This time,date is not available for this mentor"],400);
             }

                $scheduleData = [

                    'date' => $request->date,
                    'time' => $request->time,
                    'batch' => $request->batch,
                    'category_id' => $request->category_id,
                    'mentor_id'=>$request->mentor_id,
                    'course_id'=>$request->course_id

                ];


                $schedule = ClassSchedule::create($scheduleData);

                return response()->json([
                    'message' => 'Class schedule add successfully',

                ]);




            }
            else{
                return response()->json(["message"=>"You are unauthorized"],401);
            }
        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }



    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
          //
          $user = Auth::guard('api')->user();

          if ($user) {

              if ($user->userType === "SUPER ADMIN") {

                  $schedule = ClassSchedule::find($id);

                  if ($schedule) {
                      $schedule->delete();

                      return response()->json([
                          "message"=>"Class schedule deleted successfully"
                      ],200);
                      // Related classes will also be deleted due to the onDelete('cascade') constraint
                  }else{
                      return response()->json(['message' => 'Class schedule not found'], 404);
                  }


              }else{
                  return response()->json(['message' => 'You are unauthorized user'], 401);
              }



          }else {
              return response()->json(['message' => 'You are unauthorized user'], 401);
          }


    }


 public function scheduleForSuperAdmin(Request $request){

    $user = Auth::guard('api')->user();

    if ($user) {

        if ($user->userType === "SUPER ADMIN") {

            $validator = Validator::make($request->all(),[
                'batch' => 'required',
                'category_id' => 'required',
                'month'=>'required',
                'year'=>'required'
           ]);

            if ($validator->fails()){
                return response()->json(["errors"=>$validator->errors()],400);
            }

            $findSchedule=ClassSchedule::where([
                'category_id' => $request->category_id,
                'batch' => $request->batch,


            ])->with(['category', 'mentor'])
                ->where(DB::raw('YEAR(date)'), $request->year)
                ->where(DB::raw('MONTH(date)'), $request->month)
                ->get();

            if($findSchedule){
                 return response()->json([
                    "data"=>$findSchedule
                 ]);
            }else{
                return response()->json(['message' => 'Record not found'], 404);
            }

        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }
    }else {
        return response()->json(['message' => 'You are unauthorized user'], 401);
    }

  }



  public function scheduleForMentor(Request $request){
   

    $user = Auth::guard('api')->user();

    if ($user) {

        if ($user->userType === "MENTOR") {

        
            $validator = Validator::make($request->all(),[
                'batch' => 'required',
                'category_id' => 'required',
                'month'=>'required',
                'year'=>'required'
           ]);

            if ($validator->fails()){
                return response()->json(["errors"=>$validator->errors()],400);
            }

            $findSchedule=ClassSchedule::where([
                'category_id' => $request->category_id,
                'batch' => $request->batch,
                'mentor_id'=>$user->id

            ])->with(['category', 'mentor'])
                ->where(DB::raw('YEAR(date)'), $request->year)
                ->where(DB::raw('MONTH(date)'), $request->month)
                ->get();

            if($findSchedule){
                 return response()->json([
                    "data"=>$findSchedule
                 ]);
            }else{
                return response()->json(['message' => 'Record not found'], 404);
            }

        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }
    }else {
        return response()->json(['message' => 'You are unauthorized user'], 401);
    }

  }

  public function scheduleForStudent(Request $request){



    $user = Auth::guard('api')->user();

    if ($user) {

        if ($user->userType === "STUDENT") {

           

            $validator = Validator::make($request->all(),[
                'batch' => 'required',
                'month'=>'required',
                'year'=>'required',
                'course_id'=>'required'
           ]);

            if ($validator->fails()){
                return response()->json(["errors"=>$validator->errors()],400);
            }

           
           if($user->approve=="1"){
            
          
              $buyCourse=Orders::where("student_id",$user->id)->with(["course"])->get();
             //return $buyCourse->course_id;


              $courseIds = [];

                foreach ($buyCourse as $item) {
                    $courseId = $item['course_id'];
                    if (!in_array($courseId, $courseIds)) {
                        $courseIds[] = $courseId;
                    }
                }





                $findSchedule=ClassSchedule::whereIn('course_id',$courseIds)
                    ->where('batch', $request->batch)
                    ->with(['category', 'mentor'])
                    ->where(DB::raw('YEAR(date)'), $request->year)
                    ->where(DB::raw('MONTH(date)'), $request->month)
                    ->get();

                return $findSchedule;

                    $uniqueSchedules = [];
                    $processedKeys = [];
                    
                    foreach ($findSchedule as $schedule) {
                        $key = $schedule['time'] . $schedule['date'] . $schedule['batch'] . $schedule['category_id'];
                    
                        // Check if the key has been processed before
                        if (!in_array($key, $processedKeys)) {
                            $uniqueSchedules[] = $schedule;
                            $processedKeys[] = $key;
                        }
                    }  
                    return response()->json([
                        "data"=>[$uniqueSchedules,$processedKeys]
                    ]);
                    

                if($findSchedule){
                    return response()->json([
                        "data"=>$findSchedule
                    ]);
                }else{
                    return response()->json(['message' => 'Record not found'], 404);
                }

           }else{
            return response()->json(['message' => 'You are not approved user'], 401);
           }


           

        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }
    }else {
        return response()->json(['message' => 'You are unauthorized user'], 401);
    }
  }
}
