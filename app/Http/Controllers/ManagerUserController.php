<?php

namespace App\Http\Controllers;

use App\Http\Helpers\PasswordGenerator;
use App\Http\Helpers\ResponseModel;
use App\Mail\UserRegisteredMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            ]);

            Mail::to($manager->email)->send(new UserRegisteredMail($manager, $generatedPassword));

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

            $manager = User::findOrFail($data['id']);
            if ($manager->name !== $data['name'] && User::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $manager->update([
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
            $manager = User::findOrFail($id);
            $manager->active_flag = 0;
            $manager->update();

            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete manager: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
