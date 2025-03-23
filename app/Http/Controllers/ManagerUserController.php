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

class ManagerUserController extends Controller
{
    public function index() 
    {
        $managers = User::where('active_flag', 1)->where('role_id', 2)->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $managers
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
                'email' => 'required|string|max:100|min:3|unique:users,email',
            ]);

            $generatedPassword = PasswordGenerator::generatePassword();

            // Create the employee record
            $manager = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt($generatedPassword),
                'role_id' => 2,
                'createby' => Auth::id(),
            ]);

            TransactionLogger::log('users', 'create', true, "Register New Manager '{$manager->email}'");

            Mail::to($manager->email)->send(new UserRegisteredMail($manager, $generatedPassword));

            // Prepare the response
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
                'email' => 'required|string|max:100|min:3',
            ]);

            $manager = User::findOrFail($data['id']);
            if ($manager->email !== $data['email'] && User::where('email', $data['email'])->count() > 0) {
                $response = new ResponseModel(
                    'Email Already Exist',
                    1,
                    null
                );
                TransactionLogger::log('users', 'update', false, 'Email Already Exist');
                return response()->json($response, 200);
            } else {

                // Update the employee record
                $manager->update([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'updateby' => Auth::id(),
                ]);

                TransactionLogger::log('users', 'update', true, "Update Manager '{$manager->email}'");
                // Prepare the response
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
            $manager = User::findOrFail($id);
            $manager->active_flag = 0;
            $manager->updateby = Auth::id();
            $manager->update();

            TransactionLogger::log('users', 'delete', true, "Delete Manager '{$manager->email}'");
            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'delete', false, $e->getMessage());
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete manager: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
