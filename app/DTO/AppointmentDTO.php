<?php

namespace App\DTO;

class AppointmentDTO
{
    public function __construct(
        public int $doctor_id,
        public int $service_id,
        public string $from,
        public string $to,
        public string $firstname,
        public string $lastname,
        public int $status,
        public ?int $user_id = null,
        public ?int $clinic_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            doctor_id: $data['doctor_id'],
            service_id: $data['service_id'],
            from: $data['from'],
            to: $data['to'],
            firstname: $data['firstname'],
            lastname: $data['lastname'],
            status: $data['status'],
            user_id: $data['user_id'] ?? null,
            clinic_id: $data['clinic_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'doctor_id' => $this->doctor_id,
            'service_id' => $this->service_id,
            'from' => $this->from,
            'to' => $this->to,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
        ];
    }
}
