<?php

namespace App\DTO;

class DoctorDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone,
        public string $address,
        public string $password,
        public int $clinic_id,
        public array $specialization,
        public array $available_time,
    ) {}
}
