<?php

namespace App\Http\Controllers;

use App\Services\ReviewDomainService;
use App\Validators\ReviewCreateValidator;
use App\DTO\ReviewDTO;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewDomainService $service) {}

    public function index($doctorId)
    {
        return response()->json($this->service->listByDoctor($doctorId));
    }

    public function submit(Request $request)
    {
        $data = ReviewCreateValidator::validate($request->all());
        $dto = new ReviewDTO(
            $data['appointment_id'],
            $data['comment'],
            $data['rating'],
            auth('sanctum')->id()
        );

        $review = $this->service->submit($dto);

        return response()->json([
            'message' => 'Відгук успішно додано',
            'review' => $review,
        ], 201);
    }
}
