<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    public function index($doctorId)
    {
        $reviews = Review::with(['appointment.user'])
            ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($review) {
                return [
                    'id' => $review->id,
                    'comment' => $review->comment,
                    'rating' => $review->rating,
                    'appointment_id' => $review->appointment_id,
                    'created_at' => $review->created_at,
                    'user_name' => $review->appointment->user->name ?? 'Анонім',
                ];
            });

        return response()->json($reviews);

    }
    public function submit(Request $request)
    {
        $userId = auth('sanctum')->id();
        $data = $request->validate([
            'appointment_id' => 'required|integer',
            'comment' => 'required|string',
            'rating' => 'required|integer',
        ]);
        $appointment = Appointment::where('id', $request->appointment_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $review = $appointment->reviews()->create($data);

        return response()->json([
            'message' => 'Відгук успішно додано',
            'review'  => $review,
        ], 201);
    }
}
