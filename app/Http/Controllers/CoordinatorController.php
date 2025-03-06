<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Http\Helpers\ResponseModel;
use Illuminate\Http\Request;

class CoordinatorController extends Controller
{
    public function selectContribution(Request $request){

        try {
            // Validate the request
            $request->validate([
                'contribution_id' => 'required|exists:contributions,id',
            ]);

            // Get contribution
            $contribution = Contribution::findOrFail($request->contribution_id);

            // Update status in contribution table
            $contribution->update([
                'status' => 'selected',
            ]);

            // Prepare response
            $response = new ResponseModel(
                'Contribution Selected.',
                0,
                null
            );

            return response()->json($response, 200);

        } catch(\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response, 500);
        }

    }

    public function reviewContribution(Request $request){
        try {
            // Validate the request
            $request->validate([
                'contribution_id' => 'required|exists:contributions,id',
            ]);

            // Get contribution
            $contribution = Contribution::findOrFail($request->contribution_id);

            // Update status in contribution table
            $contribution->update([
                'status' => 'reviewed',
            ]);

            // Prepare response
            $response = new ResponseModel(
                'Contribution Reviewed.',
                0,
                null
            );

            return response()->json($response, 200);

        } catch(\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response, 500);
        }
    }
}
