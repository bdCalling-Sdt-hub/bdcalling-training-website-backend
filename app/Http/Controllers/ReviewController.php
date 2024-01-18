<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
        $user = Auth::guard('api')->user();

        if ($user) {
            if ($user->userType === "STUDENT") {

                $validator = Validator::make($request->all(), [
                   'course_id'=>'required | string',
                   'review'=>'required|string'
                ]);

                if ($validator->fails()) {
                    return response()->json(["errors" => $validator->errors()], 400);
                }

                $result = Review::create([
                   'course_id'=>$request->course_id,
                   'student_id'=>$user->id,
                   'review'=>$request->review
                ]);



              return response()->json([
                "message"=>'Your review add successfully'
              ],200);

            }else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        }else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $allReviews=Review::where("course_id",$id)->with(["student","course"])->get();
        foreach ($allReviews as &$review) {
            if (isset($review['course']['careeropportunities'])) {
                $review['course']['careeropportunities'] = json_decode($review['course']['careeropportunities'], true);
            }
            if (isset($review['course']['carriculum'])) {
                $review['course']['carriculum'] = json_decode($review['course']['carriculum'], true);
            }
            if (isset($review['course']['job_position'])) {
                $review['course']['job_position'] = json_decode($review['course']['job_position'], true);
            }
            if (isset($review['course']['software'])) {
                $review['course']['software'] = json_decode($review['course']['software'], true);
            }
        }
        return response()->json([
            "data"=>$allReviews
        ]);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
