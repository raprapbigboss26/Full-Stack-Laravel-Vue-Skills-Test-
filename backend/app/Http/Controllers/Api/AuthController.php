<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => false, // Default to regular user
            ]);

            // Create token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'Unable to create user account'
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $request->authenticate();

            $user = Auth::user();
            
            // Revoke all existing tokens for security
            $user->tokens()->delete();
            
            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User logged in successfully', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Login failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Login failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Login failed',
                'error' => 'Authentication error'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            Log::info('User logged out successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Logout successful'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Logout failed',
                'error' => 'Unable to logout'
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'user' => new UserResource($user)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user data', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Failed to get user data',
                'error' => 'Unable to retrieve user information'
            ], 500);
        }
    }

    /**
     * Refresh user token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Token refreshed successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            Log::error('Token refresh failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Token refresh failed',
                'error' => 'Unable to refresh token'
            ], 500);
        }
    }
}
