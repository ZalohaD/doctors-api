<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function profile($id){
        $doctor = Doctor::find($id);

        return response()->json($doctor);
    }
}
