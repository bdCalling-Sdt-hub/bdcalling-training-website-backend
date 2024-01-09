<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\StudentJourney;
use Illuminate\Support\Facades\File;

class StudentJourneyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $studentJourney = StudentJourney::orderBy('created_at', 'desc')->get();
        if ($studentJourney) {
            return response()->json([
                "message" => "Retrived all student journey video successfully",
                "data" =>$studentJourney
            ], 200);
        } else {
            return response()->json([
                "message" => "Don't have any video in student journey"
            ], 404);
        }


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

            if ($user->userType === "SUPER ADMIN") {
                $validator = Validator::make($request->all(), [
                    'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:10048',
                    'video' => 'required',

                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }


                if ($request->file('thumbnail')) {
                    $file = $request->file('thumbnail');


                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('studentJourneyImage', $fileName, 'public');

                    $filePath = '/storage/studentJourneyImage/' . $fileName;
                    $fileUrl = $filePath;


                    $studentJourneyData = [
                        'thumbnail' => $fileUrl,
                        'video'=>$request->video

                    ];


                    $StudentJourney= StudentJourney::create($studentJourneyData);
                    return response()->json([
                        'message' => 'Student Journey add successfully',

                    ]);




            }else {
                return response()->json(['message' => 'You are unauthorized user'], 401);
            }
        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::guard('api')->user();

        if ($user) {

            if ($user->userType === "SUPER ADMIN") {
                $studentJourney = StudentJourney::find($id);

                if ($studentJourney ) {
                    $studentJourney ->delete();

                    return response()->json([
                        "message"=>"Student journey data deleted successfully"
                    ],200);
                    // Related classes will also be deleted due to the onDelete('cascade') constraint
                }else{
                    return response()->json(['message' => 'Student journey data not found'], 404);
                }

            }else {
                return response()->json(['message' => 'You are unauthorized user'], 401);
            }
        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }


    }


}

