<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;

class StudentUserController extends Controller
{
    public function index()
    {
        $students = User::where('active_flag', 1)->where('role_id', 4)->with(['faculty:id,name'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $students
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

            $student = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'faculty_id' => $data['faculty_id'],
                'role_id' => 4,
            ]);

            // Mail::to($student->email)->send(new UserRegisteredMail($student));

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
                'id' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'faculty_id' => 'required|integer|exists:faculties,id',
                'email' => 'required|string|max:100|min:3',
                'password' => 'nullable|string|max:16|min:8',
            ]);

            $student = User::findOrFail($data['id']);
            if ($student->email !== $data['email'] && User::where('email', $data['email'])->count() > 0) {
                $response = new ResponseModel(
                    'Email Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                $updated = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'faculty_id' => $data['faculty_id'],
                    'role_id' => 4,
                ];

                if (!empty($data['password'])) {
                    $updated['password'] = bcrypt($data['password']);
                }

                $student->update($updated);

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
            $student = User::findOrFail($id);
            $student->active_flag = 0;
            $student->update();

            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete student: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
