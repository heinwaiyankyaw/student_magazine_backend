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

class CoordinatorUserController extends Controller
{
    public function index()
    {
        $coordinators = User::where('active_flag', 1)->where('role_id', 3)->with(['faculty:id,name'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $coordinators
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

            $coordinator = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'faculty_id' => $data['faculty_id'],
                'password' => bcrypt($generatedPassword),
                'role_id' => 3,
            ]);

            TransactionLogger::log('users', 'create', true, "Register New Coordinator '{$coordinator->email}'");

            Mail::to($coordinator->email)->send(new UserRegisteredMail($coordinator, $generatedPassword));

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
            ]);

            $coordinator = User::findOrFail($data['id']);
            if ($coordinator->email !== $data['email'] && User::where('email', $data['email'])->count() > 0) {
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
                    'role_id' => 3,
                ];

                $coordinator->update($updated);

                TransactionLogger::log('users', 'update', true, "Update Coordinator '{$coordinator->email}'");
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
            $coordinator = User::findOrFail($id);
            $coordinator->active_flag = 0;
            $coordinator->updateby = Auth::id();
            $coordinator->update();
            TransactionLogger::log('users', 'delete', true, "Delete Coordinator '{$coordinator->email}'");
            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'delete', false, $e->getMessage());
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete coordinator: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
