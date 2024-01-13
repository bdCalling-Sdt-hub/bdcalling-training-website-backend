<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
class CategoryController extends Controller
{
    //

    public function categoryAdd(Request $request){

        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

            $category=Category::where("category_name",strtolower($request->category_name))
            ->where("department_id",$request->department_id)
            ->first();
            if($category){
                return response()->json(["message"=>"This category already exists"],409);
            }else{
                $validator = Validator::make($request->all(),[
                    'category_name' => 'required|string|min:2|max:100',
                    'department_id' => 'required|exists:departments,id'

              ]);

                if ($validator->fails()){
                    return response()->json(["errors"=>$validator->errors()],400);
                }
                $user = Category::create([
                    'category_name' => strtolower($request->category_name),
                    'department_id' => $request->department_id,

                ]);

                return response()->json(["message"=>"Category created successfully"],200);
            }
        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }

        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }
    }

    public function categoryById($id){

        //return response()->json(["data"=>$id,"message"=>"Record found successfully"],200);

           $category=Category::find($id);

           if($category){
            return response()->json(["data"=>$category,"message"=>"Record found successfully"],200);
           }else{
            return response()->json(["message"=>"Record not found"],404);
           }

    }

    public function categoryUpdate(Request $request,$id){
//return response()->json(["data"=>$request->category_name]);
$user = Auth::guard('api')->user();

if ($user) {

    if ($user->userType === "SUPER ADMIN") {
            $validator = Validator::make($request->all(),[
                'category_name' => 'required|string|min:2|max:100',
             ]);

            if ($validator->fails()){
                return response()->json(["errors"=>$validator->errors()],400);
            }

            $category = Category::find($id);

            //$category=Category::where("category_name",$request->category_name)->first();
            if($category){



                if($category["category_name"]===strtolower($request->category_name)){
                    return response()->json(["message"=>"Category already exists"],409);
                }else{
                    $category->category_name=$request->category_name;
                    $category->update();
                    return response()->json(["message"=>"Category updated successfully","data"=>$category],200);
                }



            }else{
                return response()->json(["message"=>"Category doesn't exists"],409);
            }

        }else{
            return response()->json(["message"=>"Category doesn't exists"],409);
        }
        }else{
            return response()->json(["message"=>"You are unauthorized"],401);
        }
    }


    public function guard()
    {
        return Auth::guard();
    }

}
