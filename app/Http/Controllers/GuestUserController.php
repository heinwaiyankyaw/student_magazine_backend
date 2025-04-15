<?php

namespace App\Http\Controllers;

use App\Http\Helpers\PasswordGenerator;
use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use App\Mail\UserRegisteredMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class GuestUserController extends Controller
{
    public function index()
    {
        $guest = User::where('active_flag', 1)->where('role_id', 5)->with(['faculty:id,name'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $guest
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'faculty_id' => 'required|integer|exists:faculties,id',
                'email' => 'required|string|max:100|min:3|unique:users,email',
            ]);

            $generatedPassword = PasswordGenerator::generatePassword();

            $guest = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt($generatedPassword),
                'faculty_id' => $data['faculty_id'],
                'role_id' => 5,
            ]);

            TransactionLogger::log('users', 'create', true, "Register New Guest '{$guest->email}'");
            
            Mail::to($guest->email)->send(new UserRegisteredMail($guest, $generatedPassword));

            $response = new ResponseModel(
                'success',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'create', false, $e->getMessage());
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'id' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'faculty_id' => 'required|integer|exists:faculties,id',
                'email' => 'required|string|max:100|min:3',
                'password' => 'nullable|string|max:16|min:8',
            ]);

            $guest = User::findOrFail($data['id']);
            if ($guest->email !== $data['email'] && User::where('email', $data['email'])->count() > 0) {
                $response = new ResponseModel(
                    'Email Already Exist',
                    1,
                    null
                );
                TransactionLogger::log('users', 'update', false, 'Email Already Exist');
                return response()->json($response, 200);
            } else {

                $updated = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'faculty_id' => $data['faculty_id'],
                    'role_id' => 5,
                ];

                if (!empty($data['password'])) {
                    $updated['password'] = bcrypt($data['password']);
                }

                $guest->update($updated);
                TransactionLogger::log('users', 'update', true, "Update Guest '{$guest->email}'");
                $response = new ResponseModel(
                    'success',
                    0,
                    null
                );

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'update', false, $e->getMessage());
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function delete($id)
    {
        try {
            $guest = User::findOrFail($id);
            $guest->active_flag = 0;
            $guest->updateby = Auth::id();
            $guest->update();
            TransactionLogger::log('users', 'delete', true, "Delete Guest '{$guest->email}'");
            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'delete', false, $e->getMessage());
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete guest: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
