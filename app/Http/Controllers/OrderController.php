<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class OrderController extends Controller
{

    public function getAllOrders()
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            if ($user->userType === "SUPER ADMIN") {

                $orders = Orders::with(["course", "student"])->where("status", "Processing")->get();
                return $orders;
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }


    public function calculateIncome()
    {
        $today = Carbon::now()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $startOfSixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $startOfYear = Carbon::now()->startOfYear()->toDateString();


        //$total income

        $totalIncome=Orders::where("status","Processing")->sum('amount');

        // Today's income
        $todayIncome = $this->calculateIncomeByDate($today);

        // This month's income
        $thisMonthIncome = $this->calculateIncomeByDateRange($startOfMonth, $today);

        // 6-month income from today's month
        $sixMonthIncome = $this->calculateIncomeByDateRange($startOfSixMonthsAgo, $today);

        // Running year income
        $yearIncome = $this->calculateIncomeByDateRange($startOfYear, $today);

        return [
            'today_income' => $todayIncome,
            'this_month_income' => $thisMonthIncome,
            'six_month_income' => $sixMonthIncome,
            'running_year_income' => $yearIncome,
            'total_income'=>$totalIncome
        ];
    }

    protected function calculateIncomeByDate($date)
{
    return Orders::whereDate('created_at', $date)->where("status","Processing")->sum('amount');
}

protected function calculateIncomeByDateRange($startDate, $endDate)
{
    return Orders::whereDate('created_at', '>=', $startDate)
    ->whereDate('created_at', '<=', $endDate)
    ->where("status","Processing")
    ->sum('amount');
}
}
