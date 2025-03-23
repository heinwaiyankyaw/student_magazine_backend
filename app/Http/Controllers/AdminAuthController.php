<?php
namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Contribution;
use App\Models\Faculty;
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
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (! Auth::attempt([
                'email'    => $data['email'],
                'password' => $data['password'],
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
                'id'                 => $user->id,
                'first_name'         => $user->first_name,
                'last_name'          => $user->last_name,
                'email'              => $user->email,
                'profile'            => $user->profile,
                'is_password_change' => $user->is_password_change,
                'last_login_at'      => $user->last_login_at,
                'last_login_ip'      => $user->last_login_ip,
                'role_id'            => $user->role_id,
                'faculty_id'         => $user->faculty_id,
                'role_name'          => optional($user->role)->name ?? "Unknown Role",
                'faculty_name'       => optional($user->faculty)->name ?? null,
            ];

            $roles = [
                1 => "admin",
                2 => "manager",
                3 => "coordinator",
                4 => "student",
                5 => "guest",
            ];

            $response = new ResponseModel(
                'Login successful',
                0,
                [
                    'user'  => $userDetails,
                    'token' => $token,
                    'role'  => $roles[$user->role_id] ?? "unknown",
                ]
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function passwordUpdate(Request $request)
    {
        try {
            // Validate request data
            $data = $request->validate([
                'user_id'          => 'required|exists:users,id',
                'old_password'     => 'required|string|min:8',
                'new_password'     => 'required|string|min:8',
                'confirm_password' => 'required|string|same:new_password',
                'updateby'         => 'required|exists:users,id',
            ], [
                'old_password.required'     => 'Old password is required.',
                'new_password.required'     => 'New password is required.',
                'new_password.min'          => 'New password must be at least 6 characters.',
                'confirm_password.required' => 'Confirm password is required.',
                'confirm_password.same'     => 'Confirm password must match the new password.',
                'updateby.required'         => 'User ID is required.',
                'updateby.exists'           => 'User not found.',
            ]);

            // Get user by ID
            $user = User::find($data['user_id']);

            if (! $user) {
                $response = new ResponseModel(
                    'User not found.',
                    1,
                    null
                );

                return response()->json($response, 200);
            }

            // Check if old password is correct
            if (! Hash::check($data['old_password'], $user->password)) {
                $response = new ResponseModel(
                    'Old password is incorrect.',
                    1,
                    null
                );

                return response()->json($response, 200);
            }

            // Update password
            $user->update([
                'is_password_change' => true,
                'password'           => bcrypt($data['new_password']),
            ]);

            $response = new ResponseModel(
                'success',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            $response = new ResponseModel(
                'Logout successful',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function countData()
    {
        $students       = User::where('role_id', 4)->count();
        $coordinators   = User::where('role_id', 3)->count();
        $managers       = User::where('role_id', 2)->count();
        $guests         = User::where('role_id', 5)->count();
        $constributions = Contribution::count();
        $faculties      = Faculty::count();

        if (Auth::user()->role_id != 1) {
            $response = new ResponseModel(
                'Unauthorized',
                1,
                null
            );
            return response()->json($response);
        }

        $data = [
            'students'       => $students,
            'coordinators'   => $coordinators,
            'managers'       => $managers,
            'guests'         => $guests,
            'constributions' => $constributions,
            'faculties'      => $faculties,
        ];
        $response = new ResponseModel(
            'success',
            0,
            $data
        );
        return response()->json($response);
    }
}