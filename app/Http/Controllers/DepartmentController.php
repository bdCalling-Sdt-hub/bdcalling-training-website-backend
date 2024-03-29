<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    //

    public function departmentAdd(Request $request)
    {
        //return response()->json(["data"=>$request->all()]);
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
                $department = Department::where("department_name", strtolower($request->department_name))->first();
                if ($department) {
                    return response()->json(["message" => "This Department already exists"], 409);
                } else {
                    $validator = Validator::make($request->all(), [
                        'department_name' => 'required|string|min:2|max:100',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(["errors" => $validator->errors()], 400);
                    }
                    $result = Department::create([
                        'department_name' => strtolower($request->department_name),

                    ]);

                    return response()->json(["message" => "Department created successfully"], 200);
                }
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }

    public function departmentGet(Request $request)
    {


        $all = Department::all();
        //return response()->json(["data"=>$all],200);
        if ($all->isEmpty()) {
            return response()->json(["message" => "Record not found"], 404);
        } else {
            return response()->json(["data" => $all, "message" => "Record found successfully"], 200);
        }
    }


    public function departmentById($id)
    {

        //return response()->json(["data"=>$id,"message"=>"Record found successfully"],200);

        $department = Department::find($id);

        if ($department) {
            return response()->json(["data" => $department, "message" => "Record found successfully"], 200);
        } else {
            return response()->json(["message" => "Record not found"], 404);
        }
    }


    public function departmentUpdate(Request $request, $id)
    {



        $user = Auth::guard('api')->user();
        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
                

                $validator = Validator::make($request->all(), [
                    'department_name' => 'required|string|min:2|max:100',
                ]);

                if ($validator->fails()) {
                    return response()->json(["errors" => $validator->errors()], 400);
                }

                $department = Department::find($id);

                //$category=Category::where("category_name",$request->category_name)->first();
                if ($department) {



                    if ($department["department_name"] === strtolower($request->department_name)) {
                        return response()->json(["message" => "Department already exists"], 409);
                    } else {
                        $department->department_name = strtolower($request->department_name);
                        $department->update();
                        return response()->json(["message" => "Department updated successfully", "data" => $department], 200);
                    }
                }
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }




    public function guard()
    {
        return Auth::guard();
    }
}
