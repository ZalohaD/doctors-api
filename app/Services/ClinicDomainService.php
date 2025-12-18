<?php

namespace App\Services;

use App\Models\Clinic;

class ClinicDomainService
{
    public function store($data)
    {
       return Clinic::create($data);
    }

}
