<?php

namespace App\Http\Controllers;

use App\Http\Helpers\PasswordGenerator;
use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use App\Mail\UserRegisteredMail;
use App\Models\Comment;
use App\Models\Contribution;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class StudentUserController extends Controller
{
    public function index()
    {
        $students = User::where('active_flag', 1)->where('role_id', 4)->with(['faculty:id,name'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $students
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'faculty_id' => 'required|integer|exists:faculties,id',
                'email' => 'required|string|max:100|min:3|unique:users,email',
            ]);

            $generatedPassword = PasswordGenerator::generatePassword();

            $student = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt($generatedPassword),
                'faculty_id' => $data['faculty_id'],
                'role_id' => 4,
            ]);

            TransactionLogger::log('users', 'create', true, "Register New Student '{$student->email}'");

            Mail::to($student->email)->send(new UserRegisteredMail($student, $generatedPassword));

            $response = new ResponseModel(
                'success',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'create', false, $e->getMessage());
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
                'id' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'faculty_id' => 'required|integer|exists:faculties,id',
                'email' => 'required|string|max:100|min:3',
            ]);

            $student = User::findOrFail($data['id']);
            if ($student->email !== $data['email'] && User::where('email', $data['email'])->count() > 0) {
                $response = new ResponseModel(
                    'Email Already Exist',
                    1,
                    null
                );
                TransactionLogger::log('users', 'update', false, 'Email Already Exist');
                return response()->json($response, 200);
            } else {

                $updated = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'faculty_id' => $data['faculty_id'],
                    'role_id' => 4,
                ];

                $student->update($updated);
                TransactionLogger::log('users', 'update', true, "Update Student '{$student->email}'");
                $response = new ResponseModel(
                    'success',
                    0,
                    null
                );

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'update', false, $e->getMessage());
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
            $student = User::findOrFail($id);
            $student->active_flag = 0;
            $student->updateby = Auth::id();
            $student->update();
            TransactionLogger::log('users', 'delete', true, "Delete Student '{$student->email}'");
            return response()->json([
                'status' => 0,
                'message' => 'User deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            TransactionLogger::log('users', 'delete', false, $e->getMessage());
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete student: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function editArticle(Request $request, $articleId)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'closure_date' => 'required|date',
            ]);

            // Fetch article to check closure date
            $article = Article::findOrFail($articleId);

            // Ensure the closure date is not passed
            if (strtotime($article->closure_date) < strtotime(now())) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Article editing is closed after the closure date.',
                    'data' => null,
                ], 400);
            }

            // Update the article
            $article->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'closure_date' => $data['closure_date'],
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Article edited successfully.',
                'data' => $article,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }
    // Method to get all comments for an article
    public function viewComments($articleId)
    {
        try {
            $article = Article::findOrFail($articleId);
            $comments = $article->comments;  // assuming Article has a relationship to comments

            return response()->json([
                'status' => 0,
                'message' => 'Comments fetched successfully.',
                'data' => $comments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    // Method to allow student to reply to a comment
    public function respondToComment(Request $request, $articleId, $commentId)
    {
        try {
            $data = $request->validate([
                'response' => 'required|string',
            ]);

            $comment = Comment::findOrFail($commentId);
            $comment->responses()->create([
                'user_id' => auth()->user()->id,  // Assuming the user is logged in
                'response' => $data['response'],
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Response submitted successfully.',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    public function dashboard()
    {
        $user = Auth::user();

        try {
            // Get the latest 2 contributions by the authenticated student
            $contributions = Contribution::where('user_id', $user->id)
                ->latest('created_at') // Order by created_at descending
                ->take(2) // Limit to 2
                ->get();

            $contributionIds = $contributions->pluck('id');

            // Get the latest 1 comment made by a coordinator on the student's contributions
            $latestCoordinatorComment = Comment::whereIn('contribution_id', $contributionIds)
                ->whereHas('user.role', function ($query) {
                    $query->where('name', 'coordinator');
                })
                ->with(['user' => function ($q) {
                    $q->select('id', 'first_name', 'last_name'); // Select first_name and last_name
                }])
                ->latest()
                ->first();

            // Get submitArticleStatus for status counts
            $submitArticleStatus = $this->getSubmitArticleStatus($user);

            // Calculate statistics for totalSubmissions, pendingReview, and approved
            $totalSubmissions = Contribution::where('user_id', $user->id)->count();
            $pendingReview = Contribution::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
            $approved = Contribution::where('user_id', $user->id)
                ->where('status', 'selected') // Assuming 'selected' is 'approved'
                ->count();

            $data = [
                'latestCoordinatorComment' => $latestCoordinatorComment,
                'setting' => SystemSetting::first() ?? [],
                'statistics' => [
                    'contributions' => $contributions,
                    'statusCounts' => $submitArticleStatus,
                    'totalSubmissions' => $totalSubmissions,
                    'pendingReview' => $pendingReview,
                    'approved' => $approved,
                ],
            ];

            return response()->json([
                'status' => 0,
                'message' => 'Dashboard data fetched successfully.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }



    private function getSubmitArticleStatus($user)
    {
        return [
            [
                'name' => 'pending',
                'value' => Contribution::where('status', 'pending')->where('user_id', $user->id)->count(),
            ],
            [
                'name' => 'reviewed',
                'value' => Contribution::where('status', 'reviewed')->where('user_id', $user->id)->count(),
            ],
            [
                'name' => 'selected',
                'value' => Contribution::where('status', 'selected')->where('user_id', $user->id)->count(),
            ],
            [
                'name' => 'rejected',
                'value' => Contribution::where('status', 'rejected')->where('user_id', $user->id)->count(),
            ],
        ];
    }
}
