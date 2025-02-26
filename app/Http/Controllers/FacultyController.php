<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use Illuminate\Http\Request;
use App\Models\Faculty;


class FacultyController extends Controller
{
    public function index() 
    {
        $items = Faculty::where('active_flag', 1)->latest()->get();

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
            $item = Faculty::create([
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

            $item = Faculty::findOrFail($data['id']);
            if ($item->name !== $data['name'] && Faculty::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Faculty Name Already Exist',
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
            $item = Faculty::findOrFail($id);
            $item->active_flag = 0;
            $item->update();

            return response()->json([
                'status' => 0,
                'message' => 'Faculty deleted successfully.',
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
