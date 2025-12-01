<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function result(Request $request): JsonResponse
    {
        $search = trim($request->input('search'));

        if (!$search) {
            return response()->json();
        }

        $results = Doctor::query()
            ->where(function ($query) use ($search) {
                $query
                    ->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            })
            ->with('clinics')
            ->get();

        return response()->json($results);
    }

}
