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
