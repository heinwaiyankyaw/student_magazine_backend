<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
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
