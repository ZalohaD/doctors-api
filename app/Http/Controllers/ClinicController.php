<?php

namespace App\Http\Controllers;

use App\Services\ClinicDomainService;
use App\Validators\ClinicCreateValidation;
use Illuminate\Http\Request;

class ClinicController extends Controller
{

    public function __construct(
        private ClinicDomainService $service,
    )
    {}
    public function store(Request $request)
    {
        $data = ClinicCreateValidation::validate($request->all());
       $clinic = $this->service->store($data);

        return response()->json([
            'status' => 'success',
            'clinic' => $clinic
        ], 201);
    }
}
