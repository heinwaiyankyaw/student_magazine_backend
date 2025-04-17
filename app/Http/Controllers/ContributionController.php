<?php
namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use App\Mail\SubmitArticleMail;
use App\Models\Comment;
use App\Models\Contribution;
use App\Models\Notification;
use App\Models\User;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
                0,
                null
            );
            return response()->json($response);
        }

        $response = new ResponseModel(
            'success',
            0,
            $contributions
        );

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
            $contributions
        );

        return response()->json($response, 200);
    }

    public function getContributionByContributionID($id)
    {
        $contribution = Contribution::with(['faculty', 'comments'])
            ->where('active_flag', 1)
            ->find($id);

        if (! $contribution) {
            return response()->json(new ResponseModel(
                'Contribution not found',
                0,
                null
            ), 200);
        }

        return response()->json(new ResponseModel(
            'Success',
            0,
            $contribution
        ), 200);
    }

    // public function uploadArticle(Request $request)
    // {
    //     $data = $request->validate([
    //         'article'  => 'required|file|mimes:doc,docx,pdf|max:2048',
    //         'photos'   => 'array|min:1',
    //         'photos.*' => 'image|mimes:jpeg,png|max:2048',
    //         'title' => 'required',
    //         'description' => 'required',
    //         'faculty_id' => 'required'
    //     ]);

    //     $articlePath = null;
    //     if ($request->hasFile('article')) {
    //         $article     = $request->file('article');
    //         $articleName = uniqid() . '_' . $article->getClientOriginalName();
    //         $articlePath = $article->storeAs('uploads/articles', $articleName, 'public');
    //     }

    //     $imagePaths = [];
    //     if ($request->hasFile('photos')) {
    //         foreach ($request->file('photos') as $photo) {
    //             $imageName    = uniqid() . '_' . $photo->getClientOriginalName();
    //             $path         = $photo->storeAs('uploads/images', $imageName, 'public');
    //             $imagePaths[] = $path;
    //         }
    //     }

    //     // Store in database
    //     Contribution::create([
    //         'article_path' => $articlePath,
    //         'image_paths'  => json_encode($imagePaths),
    //         'title'        => $data['title'],
    //         'description'  => $data['description'],
    //         'user_id'      => Auth::id(),
    //         'faculty_id'   => $data['faculty_id'],
    //         'createby'     => Auth::id(),
    //     ]);

    //     $response = new ResponseModel(
    //         'success',
    //         0,
    //         null
    //     );

    //     return response()->json($response);
    // }

    public function uploadArticle(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'article'     => 'required|file|mimes:doc,docx,pdf|max:2048',
            'photos'      => 'array|min:1',
            'photos.*'    => 'image|mimes:jpeg,png|max:2048',
            'title'       => 'required',
            'description' => 'required',
            'faculty_id'  => 'required',
        ]);

        // Initialize S3 client
        $s3Client = new S3Client([
            'credentials'             => [
                'key'    => config('filesystems.disks.spaces.key'),
                'secret' => config('filesystems.disks.spaces.secret'),
            ],
            'region'                  => config('filesystems.disks.spaces.region'),
            'version'                 => 'latest',
            'endpoint'                => config('filesystems.disks.spaces.endpoint'),
            'use_path_style_endpoint' => false,
            'http'                    => [
                'verify' => false,
            ],
        ]);

        if ($request->hasFile('article')) {
            $article        = $request->file('article');
            $articleName    = uniqid() . '_' . $article->getClientOriginalName();
            $articleContent = file_get_contents($article->getRealPath());
            $articleType    = $article->getMimeType();

            // Upload file to S3/Spaces
            $result = $s3Client->putObject([
                'Bucket'      => config('filesystems.disks.spaces.bucket'),
                'Key'         => $articleName,
                'Body'        => $articleContent,
                'ContentType' => $articleType,
                'ACL'         => 'public-read', // Or 'private' depending on your needs
            ]);

            // Get the URL of the uploaded file
            $fileUrl = $result['ObjectURL'] ?? null;
        }

        // Upload each image file to Spaces
        $imagePaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $imageName    = uniqid() . '_' . $photo->getClientOriginalName();
                $photoContent = file_get_contents($photo->getRealPath());
                $photoType    = $photo->getMimeType();

                $result = $s3Client->putObject([
                    'Bucket'      => config('filesystems.disks.spaces.bucket'),
                    'Key'         => $imageName,
                    'Body'        => $photoContent,
                    'ContentType' => $photoType,
                    'ACL'         => 'public-read',
                ]);

                $imageUrl = $result['ObjectURL'] ?? null;
                if ($imageUrl) {
                    $imagePaths[] = $imageUrl;
                }
            }
        }

        // Store in database
        Contribution::create([
            'article_path' => $fileUrl,
            'image_paths'  => json_encode($imagePaths),
            'title'        => $data['title'],
            'description'  => $data['description'],
            'user_id'      => Auth::id(),
            'faculty_id'   => $data['faculty_id'],
            'createby'     => Auth::id(),
        ]);

        $notification = Notification::create([
            'title' => 'Article Submitted',
            'message' => 'New Article Submitted in your faculty!',
            'createby' => $user->id,
        ]);

        $coordinator = User::where('faculty_id', $data['faculty_id'])->where('role_id', 3)->first();

        // Attach notification to student
        $coordinator->notifications()->attach($notification->id, [
            'is_read' => false, // Mark as unread
            'createby' => $user->id,
        ]);

        Mail::to($coordinator->email)->send(new SubmitArticleMail($coordinator, $user));

        TransactionLogger::log('Contributions', 'upload', true, "Contribution was upload by'" . $user->first_name . " " . $user->last_name . "'");

        $response = new ResponseModel(
            'success',
            0,
            null
        );

        return response()->json($response);
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

        TransactionLogger::log('Contributions', 'edit', true, "Contribution was edit by '" . auth()->user()->first_name . " " . auth()->user()->last_name . "'");

        $response = new ResponseModel(
            'success',
            0,
            null
        );

        return response()->json($response);
    }

    public function viewComments($id)
    {
        $contribution = Contribution::with(['comments' => function ($query) {
            $query->orderBy('created_at', 'desc')->with('user');
        }])->find($id);

        if (! $contribution) {
            return response()->json(new ResponseModel(
                'Contribution not found',
                1,
                null
            ), 200);
        }

        $comments = $contribution->comments;

        if ($comments->isEmpty()) {
            return response()->json(new ResponseModel(
                'No comments found',
                0,
                []
            ), 200);
        }

        return response()->json(new ResponseModel(
            'Success',
            0,
            $comments
        ), 200);
    }

    // public function respondToComment(Request $request, $articleId, $commentId)
    // {
    //     $data = $request->validate([
    //         'comment' => 'required|string|max:255',
    //     ]);

    //     $comment = Comment::find($commentId);

    //     if (! $comment) {
    //         $response = new ResponseModel(
    //             'Comment not found.',
    //             1,
    //             null
    //         );
    //         return response()->json($response);
    //     }

    //     $data['contribution_id'] = $articleId;
    //     $data['user_id']         = Auth::id();
    //     $data['active_flag']     = 1;
    //     $data['createby']        = Auth::id();
    //     $data['updateby']        = Auth::id();
    //     $data['comment_id']      = $commentId;
    //     Comment::create($data);
    //     $response = new ResponseModel(
    //         'success',
    //         0,
    //         null
    //     );
    //     return response()->json($response);
    // }

    public function addComment(Request $request, $contributionId)
    {
        // Validate request data
        $validated = $request->validate([
            'comment'         => 'required|string|max:255',
            'contribution_id' => 'required|integer|exists:contributions,id',
        ]);

        // Check if contribution exists
        $contribution = Contribution::find($contributionId);

        if (! $contribution) {
            $response = new ResponseModel(
                'Contribution not found',
                1,
                null
            );
            return response()->json($response, 200);
        }

        // Create the comment
        $comment = Comment::create([
            'comment'         => $validated['comment'],
            'contribution_id' => $contributionId,
            'user_id'         => Auth::id(),
            'active_flag'     => true,
            'createby'        => Auth::id(),
            'updateby'        => Auth::id(),
        ]);

        TransactionLogger::log('Comments', 'create', true, "Comment was comment by '" . auth()->user()->first_name . " " . auth()->user()->last_name . "'");

        $response = new ResponseModel(
            'Comment added successfully',
            0,
            $comment
        );

        return response()->json($response, 200);
    }
}