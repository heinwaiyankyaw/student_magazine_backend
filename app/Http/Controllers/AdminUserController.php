<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index() 
    {
        $admins = User::where('active_flag', 1)->where('role_id', 1)->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $admins
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
                'password' => 'nullable|string|max:16|min:8',
            ]);

            // Create the employee record
            $admin = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id' => 2,
            ]);

            TransactionLogger::log('users', 'create', true, "Register New Admin '{$admin->email}'");

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
                'id' => 'required|exists:faculties,id',
                'name' => 'required|string|max:20|min:3',
                'description' => 'nullable'
            ]);

            $admin = User::findOrFail($data['id']);
            if ($admin->name !== $data['name'] && User::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $admin->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]);

                // Prepare the response
                $response = new ResponseModel(
                    'success',
                    0,
                    null
                );

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
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
            $admin = User::findOrFail($id);
            $admin->active_flag = 0;
            $admin->update();

            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete admin: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
