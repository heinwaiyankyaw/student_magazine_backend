<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;

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
                'password' => 'nullable|string|max:16|min:8',
            ]);

            // Create the employee record
            $coordinator = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'faculty_id' => $data['faculty_id'],
                'role_id' => 3,
            ]);

            // Prepare the response
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
            return response()->json($response, 500);
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

            $coordinator = User::findOrFail($data['id']);
            if ($coordinator->name !== $data['name'] && User::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $coordinator->update([
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
            return response()->json($response, 500);
        }
    }

    public function delete($id)
    {
        try {
            $coordinator = User::findOrFail($id);
            $coordinator->active_flag = 0;
            $coordinator->update();

            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete coordinator: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
