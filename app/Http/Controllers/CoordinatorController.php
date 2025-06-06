<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\SystemSetting;
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

            TransactionLogger::log('contributions', 'update', true, "Update contribution status as 'selected'");

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
            TransactionLogger::log('contributions', 'update', false, $e->getMessage());
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

            TransactionLogger::log('contributions', 'update', true, "Update contribution status as 'reviewed'");

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
            TransactionLogger::log('contributions', 'update', false, $e->getMessage());
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

    public function getStudentByFacultyID(){
        $user = Auth::user();

        // Get students from this faculty
        $students = User::where('active_flag', 1)
                ->where('role_id', 4)
                ->where('faculty_id', $user->faculty_id)
                ->latest()->get();

        // Prepare response
        $response = new ResponseModel(
            'success',
            0,
            $students);

        return response()->json($response, 200);
    }

    public function viewContributionDetail($id){
        try{
            // Get contribution
            $contribution = Contribution::findOrFail($id);

            // Contribution details
            $data = [
                'id' => $id,
                'title' => $contribution->title,
                'description' => $contribution->description,
                'article_path' => $contribution->article_path,
                'image_paths' => json_decode($contribution->image_paths),
                'user_id' => $contribution->student->id,
                'faculty_id' => $contribution->faculty->id,
                'status' => $contribution->status,
                'active_flag' => $contribution->active_flag,
                'created_at' => $contribution->created_at,
                'updated_at' => $contribution->updated_at,
                'updateby' => $contribution->updateby,
                'first_name' => $contribution->student->first_name,
                'last_name' => $contribution->student->last_name,
                'faculty_name' => $contribution->faculty->name,
                'comments' => $contribution->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at,
                        'user_id' => $comment->user->id,
                        'name' => $comment->user->first_name . ' ' . $comment->user->last_name,

                    ];
                }),
            ];

            // Prepare response
            $response = new ResponseModel(
                'success',
                0,
                $data);

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

    public function dashboard(){
        try {
            $user = Auth::user();

            // Get latest contributions
            $contributions = Contribution::where('contributions.active_flag', 1)
                    ->where('contributions.faculty_id', $user->faculty_id)
                    ->leftJoin('users', 'contributions.user_id', '=', 'users.id')
                    ->leftJoin('faculties', 'contributions.faculty_id', '=', 'faculties.id')
                    ->select('contributions.*', 'users.first_name', 'users.last_name', 'faculties.name as faculty_name')
                    ->latest()
                    ->take(5)
                    ->get();

            $data = [
                'contributions' => $contributions,
                'contributionCount' => Contribution::where('active_flag', 1)
                                    ->where('faculty_id', $user->faculty_id)
                                    ->count(),
                'pendingCount' => Contribution::where('active_flag', 1)
                                    ->where('faculty_id', $user->faculty_id)
                                    ->where('status', 'pending')->count(),
                'selectedCount' => Contribution::where('active_flag', 1)
                                    ->where('faculty_id', $user->faculty_id)
                                    ->where('status', 'selected')->count(),
                'systemSetting' => SystemSetting::first() ?? [],
            ];

            // Prepare response
            $response = new ResponseModel(
                'success',
                0,
                $data);

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

    public function addComment(Request $request){
        try{
            $request->validate([
                'comment' => 'required|string',
                'contribution_id' => 'required|exists:contributions,id',
            ]);

            $user = Auth::user();
            // Create comment
            $comment = Comment::create([
                'comment' => $request->comment,
                'user_id' => $user->id,
                'contribution_id' => $request->contribution_id,
                'createby' => $user->id,
            ]);

            TransactionLogger::log('comments', 'create', true, "Make comment on a contribution");

            // Get contribution
            $contribution = Contribution::findOrFail($request->contribution_id);

            //Get the student who made this contribution
            $student = $contribution->student;

            // Create notification
            $notification = Notification::create([
                'title' => 'Contribution Commented',
                'message' => "A coordinator commented on your contribution. The contribution title is '$contribution->title'",
                'createby' => $user->id,
            ]);

            // Attach notification to student
            $student->notifications()->attach($notification->id, [
                'is_read' => false, // Mark as unread
                'createby' => $user->id,
            ]);

            $comment['name'] = $user->first_name . ' ' .$user->last_name;

            // Prepare response
            $response = new ResponseModel(
                'Comment Added Successfully.',
                0,
                $comment
            );

            return response()->json($response, 200);
        } catch(\Exception $e) {
            TransactionLogger::log('comments', 'create', false, $e->getMessage());
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }
}
