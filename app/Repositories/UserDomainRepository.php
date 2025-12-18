<?php

namespace App\Repositories;

use App\Models\User;

class UserDomainRepository
{

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updateOrCreateByEmail(string $email, array $data): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            $data
        );
    }
}
