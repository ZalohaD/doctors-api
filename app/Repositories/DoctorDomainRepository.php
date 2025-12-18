<?php

namespace App\Repositories;

use App\Models\Doctor;

class DoctorDomainRepository
{
    public function create(array $data): Doctor {
        return Doctor::create($data);
    }

    public function findByEmail(string $email): ?Doctor {
        return Doctor::where('email', $email)->first();
    }
}
