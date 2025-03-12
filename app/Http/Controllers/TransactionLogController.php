<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class TransactionLogController extends Controller
{
    public function index()
    {
        try {
            $items = TransactionLog::with(['user:id,first_name,last_name,email'])->latest()->get();

            $response = new ResponseModel(
                'success',
                0,
                $items
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {

            $response = new ResponseModel(
                $e->getMessage(),
                2,
                $items
            );

            return response()->json($response);
        }
    }
}
