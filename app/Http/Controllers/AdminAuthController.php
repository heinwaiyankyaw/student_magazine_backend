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
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt([
                'email' => $data['email'],
                'password' => $data['password']
            ])) {
                $response = new ResponseModel(
                    'Invalid email or password',
                    1,
                    null
                );
                return response()->json($response);
            }

            $user = Auth::user();

            $token = $user->createToken('user-token', ['user'])->plainTextToken;

            $userDetails = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'profile' => $user->profile,
                'is_password_change' => $user->is_password_change,
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
                'role_id' => $user->role_id,
                'faculty_id' => $user->faculty_id,
                'role_name' => optional($user->role)->name ?? "Unknown Role",
                'faculty_name' => optional($user->faculty)->name ?? null,
            ];

            $roles = [
                1 => "admin",
                2 => "manager",
                3 => "coordinator",
                4 => "student",
                5 => "guest"
            ];

            $response = new ResponseModel(
                'Login successful',
                0,
                [
                    'user' => $userDetails,
                    'token' => $token,
                    'role' => $roles[$user->role_id] ?? "unknown"
                ]
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response, 500);
        }
    }
}
