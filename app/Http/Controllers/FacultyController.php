<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use Illuminate\Http\Request;
use App\Models\Faculty;
use Illuminate\Support\Facades\Log;

class FacultyController extends Controller
{
    public function index()
    {
        try {
            $items = Faculty::where('active_flag', 1)->latest()->get();

            //TransactionLogger::log('faculties', 'fetch', true, 'Fetched active faculties');

            $response = new ResponseModel(
                'success',
                0,
                $items
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {

            //TransactionLogger::log('faculties', 'fetch', false, $e->getMessage());

            $response = new ResponseModel(
                $e->getMessage(),
                2,
                $items
            );

            return response()->json($response);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100|min:3|unique:faculties,name',
                'description' => 'nullable'
            ]);

            $item = Faculty::create([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            TransactionLogger::log('faculties', 'create', true, "Create new faculty '{$item->name}'");

            $response = new ResponseModel(
                'success',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {

            TransactionLogger::log('faculties', 'create', false, $e->getMessage());

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

            $item = Faculty::findOrFail($data['id']);
            if ($item->name !== $data['name'] && Faculty::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Faculty Name Already Exist',
                    1,
                    null
                );

                TransactionLogger::log('faculties', 'update', false, 'Faculty Name Already Exist');

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $item->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]);

                TransactionLogger::log('faculties', 'update', true, "Update faculty '{$item->name}'");
                // Prepare the response
                $response = new ResponseModel(
                    'success',
                    0,
                    null
                );

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            TransactionLogger::log('faculties', 'update', false, $e->getMessage());
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
            $item = Faculty::findOrFail($id);
            $item->active_flag = 0;
            $item->update();
            TransactionLogger::log('faculties', 'delete', true, "Delete faculty '{$item->name}'");
            return response()->json([
                'status' => 0,
                'message' => 'Faculty deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            TransactionLogger::log('faculties', 'delete', false, $e->getMessage());
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
