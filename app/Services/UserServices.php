<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'] ?? true,
        ]);

        // Log the activity if a user is authenticated
        if (Auth::check()) {
            ActivityLog::log(
                Auth::id(),
                'create_user',
                "Created user: {$user->name} with role {$user->role}"
            );
        }

        return $user;
    }

    /**
     * Update an existing user
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;

        if (isset($data['password']) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        if (isset($data['status'])) {
            $user->status = $data['status'];
        }

        $user->save();

        // Log the activity if a user is authenticated
        if (Auth::check()) {
            ActivityLog::log(
                Auth::id(),
                'update_user',
                "Updated user: {$user->name}"
            );
        }

        return $user;
    }

    /**
     * Delete a user
     *
     * @param User $user
     * @return bool
     */
    public function deleteUser(User $user): bool
    {
        $name = $user->name;
        $result = $user->delete();

        // Log the activity if a user is authenticated
        if (Auth::check()) {
            ActivityLog::log(
                Auth::id(),
                'delete_user',
                "Deleted user: {$name}"
            );
        }

        return $result;
    }

    /**
     * Authenticate a user
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function authenticate(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Check if the user has an isActive method, otherwise check status directly
        if (method_exists($user, 'isActive')) {
            if (!$user->isActive()) {
                throw ValidationException::withMessages([
                    'email' => ['Your account is inactive. Please contact the administrator.'],
                ]);
            }
        } elseif (isset($user->status) && $user->status !== true && $user->status !== 1) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact the administrator.'],
            ]);
        }

        // Delete previous tokens if they exist
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log the activity
        try {
            ActivityLog::log(
                $user->id,
                'user_login',
                "User logged in: {$user->name}"
            );
        } catch (\Exception $e) {
            // Just log the error but continue with authentication
            Log::error('Failed to log login activity', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout a user
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {

        try {
            ActivityLog::log(
                $user->id,
                'user_logout',
                "User logged out: {$user->name}"
            );
        } catch (\Exception $e) {
        }

        return $user->tokens()->delete();
    }

    /**
     * Get users based on role permissions
     *
     * @param User $currentUser
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersForRole(User $currentUser)
    {
        // If isAdmin method exists, use it
        if (method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin()) {
            return User::all();
        // Otherwise check role directly
        } elseif (isset($currentUser->role) && $currentUser->role === User::ROLE_ADMIN) {
            return User::all();
        // If isManager method exists, use it
        } elseif (method_exists($currentUser, 'isManager') && $currentUser->isManager()) {
            return User::where('role', User::ROLE_STAFF)->get();
        // Otherwise check role directly
        } elseif (isset($currentUser->role) && $currentUser->role === User::ROLE_MANAGER) {
            return User::where('role', User::ROLE_STAFF)->get();
        }

        return collect();
    }
}
