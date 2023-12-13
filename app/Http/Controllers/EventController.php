<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function addEvent(Request $request)
    {
        if (auth()->user() && auth()->user()->userType == 'admin') {
            $validator = Validator::make($request->all(), [
                'course_name' => 'required|string|min:4|max:30',
                'event_image' => 'required|image',
                'event_date' => 'required|date|date_format:Y/d/m',
                'start_time' => 'required|date_format:H:i',
                'office_location' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $events = new Event();
            $events->course_name = $request->course_name;

            if ($request->file('event_image')) {
                $events->event_image = $this->saveImage($request);
            }

            $events->event_date = $request->event_date;
            $events->start_time = $request->start_time;
            $events->office_location = $request->office_location;
            $events->save();
            return response()->json('Event added successfully', 200);
        }else{
            return response()->json([
                'message' => 'User Unauthorized'
            ]);
        }
    }

        public function showEvent()
        {
            if (auth()->user() && auth()->user()->userType == 'admin') {
                $evemts = Event::all();
                return response()->json([
                    'events' => $evemts,
                ]);
            }else{
                return response()->json([
                    'message' => 'User Unauthorized'
                ]);
            }
        }

    public function singleEvent($id){
        if (auth()->user() && auth()->user()->userType == 'admin'){
            $evemts = Event::find($id);
            return response()->json([
                'events' => $evemts,
            ]);
        }else{
            return response()->json([
                'message' => 'User Unauthorized'
            ]);
        }

    }
    public function updateEvent(Request $request,$id){
        if (auth()->user() && auth()->user()->userType == 'admin') {
            $events = Event::find($id);
            if ($events) {
                $events->course_name = $request->course_name;

                if ($request->file('event_image')) {
                    if ($events->event_image) {
                        unlink($events->event_image);
                    }
                    $events->event_image = $this->saveImage($request);
                }
                $events->event_date = $request->event_date;
                $events->start_time = $request->start_time;
                $events->office_location = $request->office_location;
                $events->update();
                return response()->json([
                    'message' => 'Event update successfully',
                ]);
            } else {
                return response()->json('User does not exist', 404);
            }
        }else{
            return response()->json([
                'message' => 'User Unauthorized'
            ]);
        }
    }
    public function saveImage($request)
    {
        $image = $request->file('event_image');
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = 'adminAsset/event-image/';
        $imgUrl = $directory . $imageName;
        $image->move($directory, $imageName);
        return $imgUrl;
    }


}
