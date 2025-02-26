<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;

class ManagerUserController extends Controller
{
    public function index() 
    {
        $items = User::where('active_flag', 1)->where('role_id', 2)->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $items
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'name' => 'required|string|max:100|min:3|unique:faculties,name',
                'description' => 'nullable'
            ]);

            // Create the employee record
            $item = User::create([
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

            $item = User::findOrFail($data['id']);
            if ($item->name !== $data['name'] && User::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $item->update([
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
            $item = User::findOrFail($id);
            $item->active_flag = 0;
            $item->update();

            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
