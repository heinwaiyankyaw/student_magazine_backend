<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contribution;

class ContributionController extends Controller
{
    public function getContributionsByFacultyID(Request $request){
        $user = Auth::user();

        // Get search student name
        $studentName = $request->input('student_name', null);
        $query = Contribution::where('contributions.active_flag', 1)
                    ->where('contributions.faculty_id', $user->faculty_id)
                    ->leftJoin('users', 'contributions.user_id', '=', 'users.id');


        if ($studentName) {
            $query->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$studentName}%"]);
        }

        $contributions = $query->select('contributions.*','users.first_name','users.last_name')->latest()->get();

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

    public function getContributionsByStudentID(){
        $user = Auth::user();

        $contributions = Contribution::where('active_flag', 1)
                                    ->where('user_id', $user->id)
                                    ->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $contributions);

        return response()->json($response, 200);
    }

    public function uploadArticle(Request $request) {
        $data = $request->validate([
            'article' => 'required|file|mimes:doc,docx,pdf|max:2048',
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png|max:2048'
        ]);

        $articlePath = null;
        if ($request->hasFile('article')) {
            $article = $request->file('article');
            $articleName = uniqid() . '_' . $article->getClientOriginalName();
            $articlePath = $article->storeAs('uploads/articles', $articleName, 'public');
        }

        $imagePaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $imageName = uniqid() . '_' . $photo->getClientOriginalName();
                $path = $photo->storeAs('uploads/images', $imageName, 'public');
                $imagePaths[] = $path;
            }
        }

        // Store in database
        Contribution::create([
            'article_path' => $articlePath,
            'image_paths' => json_encode($imagePaths),
            'title' => "My contribution",
            'description' => "My contribution",
            'user_id' => 1,
            'faculty_id' => 1,
            'createby' => 1,
        ]);

        $response = new ResponseModel(
            'success',
            0,
            null);

        return response()->json([
           $response
        ]);
    }

}
