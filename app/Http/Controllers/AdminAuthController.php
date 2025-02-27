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

            if ($user->role_id == 1) {
                $role = "admin";
            }
            if ($user->role_id == 2) {
                $role = "manager";
            }  
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
