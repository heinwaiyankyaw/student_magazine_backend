<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Http\Helpers\ResponseModel;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            //Get the student who made this contribution
            $student = $contribution->student;

            $user = Auth::user();

            // Create notification
            $notification = Notification::create([
                'title' => 'Contribution Selected',
                'message' => 'Your contribution has been selected!',
                'createby' => $user->id,
            ]);

            // Attach notification to student
            $student->notifications()->attach($notification->id, [
                'is_read' => false, // Mark as unread
                'createby' => $user->id,
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
            return response()->json($response);
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

            //Get the student who made this contribution
            $student = $contribution->student;

            $user = Auth::user();

            // Create notification
            $notification = Notification::create([
                'title' => 'Contribution Reviewed',
                'message' => 'Your contribution has been reviewed!',
                'createby' => $user->id,
            ]);

            // Attach notification to student
            $student->notifications()->attach($notification->id, [
                'is_read' => false, // Mark as unread
                'createby' => $user->id,
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
            return response()->json($response);
        }
    }

    public function getGuestByFacultyID(){
        $user = Auth::user();

        // Get guests from this faculty
        $guests = User::where('active_flag', 1)
                    ->where('role_id', 5)
                    ->where('faculty_id', $user->faculty_id)
                    ->latest()->get();

        // Prepare response
        $response = new ResponseModel(
            'success',
            0,
            $guests);

        return response()->json($response, 200);
    }
}
