<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contribution;

class ContributionController extends Controller
{
    public function getContributionsByFacultyID(){
        $user = Auth::user();

        $contributions = Contribution::where('active_flag', 1)
                                    ->where('faculty_id', $user->faculty_id)
                                    ->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $contributions);

        return response()->json($response, 200);
    }
}
