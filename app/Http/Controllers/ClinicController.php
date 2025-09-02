<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'phone' => 'required|string|max:255',
        ]);
        $clinic = Clinic::create($data);

        return response()->json([
            'status' => 'success',
            'clinic' => $clinic
        ], 201);
    }
}
