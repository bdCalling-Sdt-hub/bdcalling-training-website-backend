<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    //

    public function addClassSchedule(Request $request){
        $validator = Validator::make($request->all(), [
            'course_time' => 'required|date_format:H:i|unique:schedules',
            'course_date' => 'required|date|date_format:Y/d/m',
            'course_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $schedule = new Schedule();
        $schedule->course_time = $request->course_time;
        $schedule->course_date = $request->course_date;
        $schedule->course_name = $request->course_name;
        $schedule->save();
        return response()->json('Schedule added successfully',200);
    }

    public function getWeeklyCalendar()
    {
        $today = Carbon::now();
        $endOfWeek = $today->copy()->endOfWeek();
        $startOfWeek = $today->copy()->startOfWeek();

        $weeklySchedule = Schedule::where('created_at', '>=', $startOfWeek)
            ->where('updated_at', '<=', $endOfWeek)
            ->get();

        $groupedSchedule = $weeklySchedule->groupBy(function ($event) {
            return Carbon::parse($event->start_time)->format('Y-m-d');
        });

        return response()->json($groupedSchedule);
    }
}
