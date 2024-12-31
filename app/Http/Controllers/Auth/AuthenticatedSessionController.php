<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // Attempt to authenticate the user using the provided credentials
        if (Auth::attempt($request->only('email', 'password'))) {
            // User successfully authenticated, generate a token for the user
            $user = Auth::user();

            // Ensure the user model uses HasApiTokens
            if (!method_exists($user, 'createToken')) {
                return response()->json(['message' => 'Token creation method not found'], 500);
            }

            // Create the API token
            $token = $user->createToken('Laravel')->plainTextToken;

            // Return the token and user details
            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'license_plate' => $user->license_plate,
                    'role_id' => $user->role_id
                ]
            ], 200);
        }

        // If authentication fails, return an error response
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
