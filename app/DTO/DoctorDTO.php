<?php

namespace App\DTO;

class DoctorDTO
{
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?int $clinic_id = null,
        public ?array $specializations = null,
        public ?array $available_time = null,
        public ?array $services = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            clinic_id: $data['clinic_id'] ?? null,
            specializations: $data['specializations'] ?? null,
            available_time: $data['available_time'] ?? null,
            services: $data['services'] ?? null,
        );
    }
}
