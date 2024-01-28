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

        $user = Auth::guard('api')->user();

        if ($user) {
            if ($user->userType === "SUPER ADMIN") {
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

            }else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
        }else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }


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



public function incomeShowByChart(Request $request){

    $user = Auth::guard('api')->user();

    if ($user) {
        if ($user->userType === "SUPER ADMIN") {

            $walletData =Orders::with(["course", "student"])->where("status", "Processing")->get();// your wallet response array here;
            $filterOption = $request->input('data');

            if($filterOption=="month"){
                $currentYear = Carbon::now()->format('Y');

        // Initialize an array with months in the range
        $monthsInRange = array_map(function ($month) {
            return Carbon::parse("2022-$month-01")->format('F');
        }, range(1, 12));

        $filteredDataByMonth = array_fill_keys($monthsInRange, '');

        foreach ($walletData as $transaction) {
            $transactionMonth = Carbon::parse($transaction['created_at'])->format('F');

            // Filter by month in the running year
            if ($filterOption === 'month' && array_key_exists($transactionMonth, $filteredDataByMonth)) {
                // If there is income for the month, update the amount
                if (isset($filteredDataByMonth[$transactionMonth]) && is_numeric($filteredDataByMonth[$transactionMonth])) {
                    $filteredDataByMonth[$transactionMonth] += $transaction['amount'];
                } else {
                    $filteredDataByMonth[$transactionMonth] = $transaction['amount'];
                }
            }
        }

        // Transform the array into the desired format
        $result = collect($filteredDataByMonth)
            ->map(function ($income, $month) {
                return ['month' => strtolower(substr($month, 0, 3)), 'income' => $income];
            })
            ->values()
            ->toArray();

        return response()->json($result);
            }
            else if($filterOption=="year"){
                $currentYear = Carbon::now()->format('Y');

                // Initialize an array with years in the range
                $yearsInRange = range($currentYear - 12, $currentYear);
                $filteredDataByYear = array_fill_keys($yearsInRange, '');

                foreach ($walletData as $transaction) {
                    $transactionYear = Carbon::parse($transaction['created_at'])->format('Y');

                    // Filter by previous 12 years
                    if ($filterOption === 'year' && array_key_exists($transactionYear, $filteredDataByYear)) {
                        // If there is income for the year, update the amount
                        if (isset($filteredDataByYear[$transactionYear]) && is_numeric($filteredDataByYear[$transactionYear])) {
                            $filteredDataByYear[$transactionYear] += $transaction['amount'];
                        } else {
                            $filteredDataByYear[$transactionYear] = $transaction['amount'];
                        }
                    }
                }

                // Transform the array into the desired format
                $result = collect($filteredDataByYear)
                    ->map(function ($income, $year) {
                        return ['year' => (int)$year, 'income' => $income];
                    })
                    ->values()
                    ->toArray();

                return response()->json($result);
            }


        }else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }else {
        return response()->json(["message" => "You are unauthorized"], 401);
    }

}
}
