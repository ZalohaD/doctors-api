<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function result(Request $request): JsonResponse
    {
        $search = $request->input('q');
        $results = Doctor::query()
            ->where('first_name', 'LIKE', "%$search%")
            ->orWhere('last_name', 'LIKE', "%$search%")
            ->with(['clinics'])
            ->get();
        return response()->json($results);
    }
}
