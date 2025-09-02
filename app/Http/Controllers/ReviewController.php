<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'appointment_id' => 'required|integer',
            'comment' => 'required|string',
            'rating' => 'required|integer',
        ]);
       $rating = Review::create($data);
        return response()->json(['rating' => $rating, 'message' => 'Your rating has been submitted!']);

    }
}
