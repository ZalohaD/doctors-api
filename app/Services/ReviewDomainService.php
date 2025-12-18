<?php
namespace App\Services;

use App\Models\Review;
use App\Models\Appointment;
use App\DTO\ReviewDTO;
use Illuminate\Validation\ValidationException;

class ReviewDomainService
{
    public function listByDoctor(int $doctorId)
    {
        $reviews = Review::with(['appointment.user'])
            ->whereHas('appointment', fn($q) => $q->where('doctor_id', $doctorId))
            ->orderBy('created_at', 'desc')
            ->get();

        return $reviews->map(fn($review) => [
            'id' => $review->id,
            'comment' => $review->comment,
            'rating' => $review->rating,
            'appointment_id' => $review->appointment_id,
            'created_at' => $review->created_at,
            'user_name' => $review->appointment->user->name ?? 'Anonym',
        ]);
    }

    public function submit(ReviewDTO $dto)
    {
        $appointment = Appointment::where('id', $dto->appointmentId)
            ->where('user_id', $dto->userId)
            ->first();

        if (!$appointment) {
            throw ValidationException::withMessages(['appointment_id' => 'Invalid appointment']);
        }

        return $appointment->reviews()->create([
            'comment' => $dto->comment,
            'rating' => $dto->rating,
        ]);
    }
}
