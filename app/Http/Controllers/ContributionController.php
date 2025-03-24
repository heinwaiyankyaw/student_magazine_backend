<?php
namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Comment;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContributionController extends Controller
{
    public function getContributionsByFacultyID(Request $request)
    {
        $user = Auth::user();

        // Get search student name
        $studentName = $request->input('student_name', null);
        $query       = Contribution::where('contributions.active_flag', 1)
            ->where('contributions.faculty_id', $user->faculty_id)
            ->leftJoin('users', 'contributions.user_id', '=', 'users.id')
            ->leftJoin('faculties', 'contributions.faculty_id', '=', 'faculties.id');

        if ($studentName) {
            $query->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$studentName}%"]);
        }

        $contributions = $query->select('contributions.*', 'users.first_name', 'users.last_name', 'faculties.name as faculty_name')->latest()->get();

        // If there is no contribution
        if ($contributions->isEmpty()) {
            $response = new ResponseModel(
                'No contributions found.',
                1,
                null);
            return response()->json($response);
        }

        $response = new ResponseModel(
            'success',
            0,
            $contributions);

        return response()->json($response, 200);
    }

    public function getContributionsByStudentID()
    {
        $user = Auth::user();

        $contributions = Contribution::where('active_flag', 1)
            ->where('user_id', $user->id)
            ->with('faculty', 'comments')
            ->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $contributions);

        return response()->json($response, 200);
    }

    public function uploadArticle(Request $request)
    {
        $data = $request->validate([
            'article'  => 'required|file|mimes:doc,docx,pdf|max:2048',
            'photos'   => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png|max:2048',
        ]);

        $articlePath = null;
        if ($request->hasFile('article')) {
            $article     = $request->file('article');
            $articleName = uniqid() . '_' . $article->getClientOriginalName();
            $articlePath = $article->storeAs('uploads/articles', $articleName, 'public');
        }

        $imagePaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $imageName    = uniqid() . '_' . $photo->getClientOriginalName();
                $path         = $photo->storeAs('uploads/images', $imageName, 'public');
                $imagePaths[] = $path;
            }
        }

        // Store in database
        Contribution::create([
            'article_path' => $articlePath,
            'image_paths'  => json_encode($imagePaths),
            'title'        => "My contribution",
            'description'  => "My contribution",
            'user_id'      => 1,
            'faculty_id'   => 1,
            'createby'     => 1,
        ]);

        $response = new ResponseModel(
            'success',
            0,
            null);

        return response()->json([
            $response,
        ]);
    }

    public function editArticle(Request $request, $id)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'article'     => 'nullable|file|mimes:doc,docx,pdf|max:2048',
            'photos'      => 'nullable|array|min:1',
            'photos.*'    => 'image|mimes:jpeg,png|max:2048',
        ]);

        $contribution = Contribution::find($id);

        if ($request->hasFile('article')) {
            $article                    = $request->file('article');
            $articleName                = uniqid() . '_' . $article->getClientOriginalName();
            $articlePath                = $article->storeAs('uploads/articles', $articleName, 'public');
            $contribution->article_path = $articlePath;
        }

        if ($request->hasFile('photos')) {
            $imagePaths = [];
            foreach ($request->file('photos') as $photo) {
                $imageName    = uniqid() . '_' . $photo->getClientOriginalName();
                $path         = $photo->storeAs('uploads/images', $imageName, 'public');
                $imagePaths[] = $path;
            }
            $contribution->image_paths = json_encode($imagePaths);
        }

        $contribution->title       = $data['title'];
        $contribution->description = $data['description'];
        $contribution->updateby    = Auth::id();
        $contribution->save();

        $response = new ResponseModel(
            'success',
            0,
            null);

        return response()->json($response);
    }

    public function viewComments($id)
    {
        $contribution = Contribution::find($id);

        if (! $contribution) {
            $response = new ResponseModel(
                'Contribution not found.',
                1,
                null);
            return response()->json($response);
        }

        $comments = $contribution->comments()->with('contribution')->whereNull('comment_id')->get();

        if ($comments->isEmpty()) {
            $response = new ResponseModel(
                'No comments found.',
                1,
                null);
            return response()->json($response);
        }

        $response = new ResponseModel(
            'success',
            0,
            $comments);

        return response()->json($response, 200);
    }

    public function respondToComment(Request $request, $articleId, $commentId)
    {
        $data = $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $comment = Comment::find($commentId);

        if (! $comment) {
            $response = new ResponseModel(
                'Comment not found.',
                1,
                null);
            return response()->json($response);
        }

        $data['contribution_id'] = $articleId;
        $data['user_id']         = Auth::id();
        $data['active_flag']     = 1;
        $data['createby']        = Auth::id();
        $data['updateby']        = Auth::id();
        $data['comment_id']      = $commentId;
        Comment::create($data);
        $response = new ResponseModel(
            'success',
            0,
            null);
        return response()->json($response);
    }

}
