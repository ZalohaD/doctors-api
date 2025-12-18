<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserDomainRepository;
use App\DTO\UserDTO;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserDomainService {
    public function __construct(private UserDomainRepository $repo) {}


    public function getCurrentUser(): User
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            throw new \DomainException('Unauthorized');
        }

        return $user;
    }


    public function register(UserDTO $dto): array {
        $user = $this->repo->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password): array {
        $user = $this->repo->findByEmail($email);
        if(!$user || !Hash::check($password, $user->password)){
            throw ValidationException::withMessages(['email'=>['Invalid credentials']]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return ['user'=>$user, 'token'=>$token];
    }

    public function logout($user): void {
        $user->currentAccessToken()->delete();
    }

    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleAuth() {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = $this->repo->updateOrCreateByEmail($googleUser->getEmail(), [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(10)),
                'avatar' => $googleUser->getAvatar(),
            ]);
            $token = $user->createToken('api_token')->plainTextToken;
            return redirect()->away(
                config('google_base_url').'/auth/callback?token='.$token.'&user='.urlencode(json_encode($user))
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->away(config('google_base_url').'/login?error='.urlencode($e->getMessage()));
        }
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user->refresh();
    }

    public function getAppointments(User $user)
    {
        return $user->appointments()->get();
    }

}
