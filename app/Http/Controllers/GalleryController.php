<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Gallery;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $allGallery = Gallery::orderBy('created_at', 'desc')->get();
        if ($allGallery) {
            return response()->json([
                "message" => "Retrived all gallery successfully",
                "data" =>$allGallery
            ], 200);
        } else {
            return response()->json([
                "message" => "Don't have any image in Gallery"
            ], 404);
        }


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

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
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10048',

                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 400);
                }


                if ($request->file('image')) {
                    $file = $request->file('image');


                    $timeStamp = time(); // Current timestamp
                    $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('galleryImage', $fileName, 'public');

                    $filePath = '/storage/galleryImage/' . $fileName;
                    $fileUrl = $filePath;


                    $galleryData = [
                        'image' => $fileUrl,


                    ];


                    $gallery = Gallery::create($galleryData);
                    return response()->json([
                        'message' => 'Gallery image add successfully',

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
                $gallery = Gallery::find($id);

                if ($gallery) {
                    $gallery->delete();

                    return response()->json([
                        "message"=>"Gallery image deleted successfully"
                    ],200);
                    // Related classes will also be deleted due to the onDelete('cascade') constraint
                }else{
                    return response()->json(['message' => 'Gallery image not found'], 404);
                }

            }else {
                return response()->json(['message' => 'You are unauthorized user'], 401);
            }
        }else {
            return response()->json(['message' => 'You are unauthorized user'], 401);
        }


    }
}
