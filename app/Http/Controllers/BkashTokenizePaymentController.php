<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Karim007\LaravelBkashTokenize\Facade\BkashPaymentTokenize;
use Karim007\LaravelBkashTokenize\Facade\BkashRefundTokenize;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use DB;
use App\Models\Course;
use App\Models\Orders;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Illuminate\Support\Facades\Cache;


class BkashTokenizePaymentController extends Controller
{
    public function index()
    {
        return view('bkashT::bkash-payment');
    }
    public function createPayment(Request $request)
    {

        $user = Auth::guard('api')->user();


        if ($user) {

            if ($user->userType === "STUDENT") {

               Cache::put("bkash_amount",$request->price,now()->addMinutes(1));
               Cache::put("gateway_name",$request->gateway_name,now()->addMinutes(1));
               Cache::put("course_id",$request->course_id,now()->addMinutes(1));
               Cache::put("student_id",$user["id"],now()->addMinutes(1));
               

                // cache(['bkash_amount' => $request->price], now()->addMinutes(1));
                // cache(['gateway_name' => $request->gateway_name], now()->addMinutes(1));
                // cache(['course_id' => $request->course_id], now()->addMinutes(1));
                // cache(['student_id' => $user["id"]], now()->addMinutes(1));


                //cache(['bkash_amount' => $request->price], now()->addMinutes(10));

                $inv = uniqid();
                $request['intent'] = 'sale';
                $request['mode'] = '0011'; //0011 for checkout
                $request['payerReference'] = $inv;
                $request['currency'] = 'BDT';
                $request['amount'] = $request->price;
                $request['merchantInvoiceNumber'] = $inv;
                $request['callbackURL'] = config("bkash.callbackURL");

                $request_data_json = json_encode($request->all());

                $response =  BkashPaymentTokenize::cPayment($request_data_json);
                //$response =  BkashPaymentTokenize::cPayment($request_data_json,1); //last parameter is your account number for multi account its like, 1,2,3,4,cont..

                //store paymentID and your account number for matching in callback request
                // dd($response) //if you are using sandbox and not submit info to bkash use it for 1 response

                if (isset($response['bkashURL'])) return $response['bkashURL'];
                else return redirect()->back()->with('error-alert2', $response['statusMessage']);
            } else {
                return response()->json(["message" => "You are unauthorized"], 401);
            }
        } else {
            return response()->json(["message" => "You are unauthorized"], 401);
        }
    }

    public function callBack(Request $request)
    {
        //callback request params
        // paymentID=your_payment_id&status=success&apiVersion=1.2.0-beta
        //using paymentID find the account number for sending params

        if ($request->status == 'success') {
            $response = BkashPaymentTokenize::executePayment($request->paymentID);
            //$response = BkashPaymentTokenize::executePayment($request->paymentID, 1); //last parameter is your account number for multi account its like, 1,2,3,4,cont..
            if (!$response) { //if executePayment payment not found call queryPayment
                $response = BkashPaymentTokenize::queryPayment($request->paymentID);
                //$response = BkashPaymentTokenize::queryPayment($request->paymentID,1); //last parameter is your account number for multi account its like, 1,2,3,4,cont..
            }

            if (isset($response['statusCode']) && $response['statusCode'] == "0000" && $response['transactionStatus'] == "Completed") {
                /*
                 * for refund need to store
                 * paymentID and trxID
                 * */
               
                 $amount=Cache::get("bkash_amount");
                 $course_id=Cache::get("course_id");
                 $student_id=Cache::get("student_id");
                 $gateway_name=Cache::get("gateway_name");

                // $amount = cache('bkash_amount');
                // $course_id = cache("course_id");
                // $student_id = cache("student_id");
                // $gateway_name = cache("gateway_name");

                return response()->json([
                    $amount, $course_id, $student_id, $gateway_name
                ]);

                $orders = Orders::create([
                    'amount' => $amount,
                    'gateway_name' => $gateway_name,
                    'course_id' => $course_id,
                    'transaction_id' => $response['trxID'],
                    'student_id' => $student_id,
                    'status' => "Processing",
                    'currency' => "BDT",

                ]);

                // return response()->json([
                //     "data" => $orders,

                // ]);

                // $course = Course::find($course_id);



                // $data = [
                //     "amount" => $amount,
                //     "gateway_name" => $gateway_name,
                //     "course_id" => $course_id,
                //     "course_name" => $course->courseName,
                //     "transaction_id" => $response['trxID'],
                //     "student_id" => $student_id,
                //     "status" => "Processing",
                //     "currency" => "BDT"
                // ];
                //Log::info($data);




                //DB::table("orders")->insert($data);

                $student = User::find($student_id);
                $student->course_id = $course_id;
                $student->approve = 1;
                $student->update();

                return Redirect::away('http://192.168.10.16:3000/payment/status/success');
                //return BkashPaymentTokenize::success('Thank you for your payment', $response['trxID']);
            }
            //return BkashPaymentTokenize::failure($response['statusMessage']);
            return Redirect::away('http://192.168.10.16:3000/payment/status/failed');
        } else if ($request->status == 'cancel') {
            return BkashPaymentTokenize::cancel('Your payment is canceled');
        } else {
            return BkashPaymentTokenize::failure('Your transaction is failed');
        }
    }


    public function searchTnx($trxID)
    {
        //response
        return BkashPaymentTokenize::searchTransaction($trxID);
        //return BkashPaymentTokenize::searchTransaction($trxID,1); //last parameter is your account number for multi account its like, 1,2,3,4,cont..
    }

    // public function refund(Request $request)
    // {
    //     $paymentID='Your payment id';
    //     $trxID='your transaction no';
    //     $amount=5;
    //     $reason='this is test reason';
    //     $sku='abc';
    //     //response
    //     return BkashRefundTokenize::refund($paymentID,$trxID,$amount,$reason,$sku);
    //     //return BkashRefundTokenize::refund($paymentID,$trxID,$amount,$reason,$sku, 1); //last parameter is your account number for multi account its like, 1,2,3,4,cont..
    // }
    // public function refundStatus(Request $request)
    // {
    //     $paymentID='Your payment id';
    //     $trxID='your transaction no';
    //     return BkashRefundTokenize::refundStatus($paymentID,$trxID);
    //     //return BkashRefundTokenize::refundStatus($paymentID,$trxID, 1); //last parameter is your account number for multi account its like, 1,2,3,4,cont..
    // }


    public function executePayment(Request $request)
    {
        $paymentID = $request->paymentID;
        return BkashPayment::executePayment($paymentID);
    }


    public function queryPayment(Request $request)
    {
        $paymentID = $request->payment_info['payment_id'];
        return BkashPayment::queryPayment($paymentID);
    }
}
