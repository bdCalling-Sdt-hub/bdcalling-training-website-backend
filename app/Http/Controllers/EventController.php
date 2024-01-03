<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $allEvents = Event::get();
        if ($allEvents) {
            return response()->json([
                "message" => "Retrived all event successfully",
                "data" => $allEvents
            ], 200);
        } else {
            return response()->json([
                "message" => "Don't have any Event"
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
        //

        $user = Auth::guard('api')->user();


        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                $validator = Validator::make($request->all(), [
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048',
                    'date' => 'required | date',
                    'time' => 'required|date_format:H:i|after:08:00|before:22:00',
                    'officeLocation' => 'required|string',
                    'courseName' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }


                if ($request->file('image')) {
                    $file = $request->file('image');


                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('eventImage', $fileName, 'public');

                    $filePath = '/storage/eventImage/' . $fileName;
                    $fileUrl = $filePath;


                    $eventData = [
                        'image' => $fileUrl,
                        'date' => $request->date,
                        'time' => $request->time,
                        'officeLocation' => $request->officeLocation,
                        'courseName' => $request->courseName,

                    ];


                    $event = Event::create($eventData);
                    return response()->json([
                        'message' => 'Event add successfully',
                        'user' => $eventData,
                    ]);
                }
            } else {
                return response()->json(['message' => 'You are unauthorized user'], 401);
            }
        } else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

        $singleEvent = Event::find($id);
        if ($singleEvent) {
            return response()->json([
                "message" => "Retrived event successfully",
                "data" => $singleEvent
            ], 200);
        } else {
            return response()->json([
                "message" => "Not found any Event"
            ], 404);
        }
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

        $user = Auth::guard('api')->user();


        if ($user) {

            if ($user->userType === "SUPER ADMIN") {

                
            }
        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}