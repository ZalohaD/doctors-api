<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function result(Request $request)
    {
        $search = $request->input('search');
        $results = Doctor::query()
            ->where('first_name', 'LIKE', "%$search%")
            ->orWhere('last_name', 'LIKE', "%$search%")
            ->orWhereHas('specializations', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->with(['clinics', 'specializations'])
            ->get();
        return response()->json($results);
    }
}
