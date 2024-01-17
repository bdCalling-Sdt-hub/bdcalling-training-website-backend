<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use App\Mail\ContactMail;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'phone' => 'required|string|regex:/^\d{11}$/',
            'email'=>'required|string|email|min:5',
            "category"=>'required|string',
            "details"=>'required|string|max:3000'
        ]);

        if ($validator->fails()){
            return response()->json(["errors"=>$validator->errors()],400);
        }

        $mailData=[
            'name' => $request->name,
            'phone' => $request->phone,
            'email'=>$request->email,
            "category"=>$request->category,
            "details"=>$request->details

           ];

           Mail::to("freelancerrtushar@gmail.com")->send(new ContactMail($mailData));

           //dd("Email send successfully");
           return response()->json(["data"=>"Your Contact information sent successfully"]);

        return response()->json([
            "data"=>$request->all()
        ]);

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
        //
    }
}
