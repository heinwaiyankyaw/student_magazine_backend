<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    public function dashboard()
    {
        try {
            $user = Auth::user();

            $contributions = Contribution::where('contributions.active_flag', 1)
            ->where('contributions.faculty_id', $user->faculty_id)
            ->leftJoin('users', 'contributions.user_id', '=', 'users.id')
            ->leftJoin('faculties', 'contributions.faculty_id', '=', 'faculties.id')
            ->select('contributions.*', 'users.first_name', 'users.last_name', 'faculties.name as faculty_name')
            ->latest()
            ->take(5)
            ->get();

            $data = [
                "contribution_count" => $contributions,

            ];

            $response = new ResponseModel(
                'success',
                0,
                $data);

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function index()
    {
        $user = Auth::user();

        $contributions = Contribution::where('active_flag', 1)->where('faculty_id', $user->faculty_id)->where('status', 'selected')->with(['faculty:id,name', 'student:id,first_name,last_name'])->latest()->get();

        return response()->json(new ResponseModel(
            'Success',
            0,
            $contributions
        ));
    }
}
