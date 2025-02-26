<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{

    public function login(Request $request)
{
    try {
        // Validate incoming request data
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find user by email
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            // If user not found, return an error response
            $response = new ResponseModel(
                'Invalid email or password',
                1,
                null
            );
            return response()->json($response, 401); // Unauthorized
        }

        // Check if the password matches the plain text password in the database
        if ($data['password'] !== $user->password) {
            // If the password does not match
            $response = new ResponseModel(
                'Invalid email or password',
                1,
                null
            );
            return response()->json($response, 401); // Unauthorized
        }

        // If the password matches, log the user in
        Auth::login($user);

        // Create a token for the authenticated user
        $token = $user->createToken('user-token')->plainTextToken;

        if($user->role_id == 1){
            $role = "admin";
            }
        // Prepare the success response with user data and token
        $response = new ResponseModel(
            'Login successful',
            0,
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'role' => $role
                
            ]
        );

        return response()->json($response, 200); // OK response
    } catch (\Exception $e) {
        // Catch any exceptions and return a response with error message
        $response = new ResponseModel(
            $e->getMessage(),
            2,
            null
        );
        return response()->json($response, 500); // Internal server error
    }
}

}
