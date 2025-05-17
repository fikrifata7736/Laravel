<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle user login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validate the request input
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Authenticate the user using the service
            $result = $this->userService->authenticate(
                $request->email,
                $request->password
            );

            // Return the successful response
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $result['user']->only(['id', 'name', 'email', 'role']),
                'token' => $result['token'],
            ]);

        } catch (ValidationException $e) {
            // Handle validation errors from the service
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log the error but don't expose details to client
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a generic error message to the client
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please check your email and password.',
            ], 401);
        }
    }

    /**
     * Handle user logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $this->userService->logout($user);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Get the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'user' => $user->only(['id', 'name', 'email', 'role']),
            ]);
        } catch (\Exception $e) {
            Log::error('Auth-me error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user information',
            ], 500);
        }
    }
}
