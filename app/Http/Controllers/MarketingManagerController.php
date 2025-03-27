<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\Messages;
use Illuminate\Http\Request;
use ZipArchive;
use Storage;

class MarketingManagerController extends Controller
{
    // Dashboard statistics
    public function index() {
        try {
            // Get total count of all contributions
            $totalContributions = Contribution::count();

            // Get count of approved contributions (selected for publication)
            $approvedContributions = Contribution::where('status', 'approved')->count();

            // Get count of pending contributions
            $pendingContributions = Contribution::where('status', 'pending')->count();

            // Get count of unreviewed contributions
            $unreviewedContributions = Contribution::whereNull('comments')->count();

            // Prepare the data
            $data = [
                'total_contributions' => $totalContributions,
                'approved_contributions' => $approvedContributions,
                'pending_contributions' => $pendingContributions,
                'unreviewed_contributions' => $unreviewedContributions,
            ];

            // ResponseModel to return to the view
            $response = new ResponseModel(
                (new Messages())->success,
                true,
                $data
            );

            return response()->json($response);
        } catch (\Exception $e) {
            // In case of an error, return a failure response
            $response = new ResponseModel(
                (new Messages())->fail,
                false,
                null
            );

            return response()->json($response); // Internal server error
        }
    }

    public function selectedArticles() {
        $articles = Contribution::where('active_flag', 1)->where('status', 'selected')->latest()->get();
        $response = new ResponseModel(
            'success',
            0,
            $articles
        );
        return response()->json($response);
    }

    // View all approved contributions
    public function viewContributions(Request $request) {
        try {
            // Get filter criteria from the request
            $studentName = $request->input('student_name', null);
            $date = $request->input('date', null);

            // Query approved contributions, filter by student name or date
            $query = Contribution::where('status', 'approved');

            if ($studentName) {
                $query->whereHas('student', function($q) use ($studentName) {
                    $q->where('name', 'LIKE', '%' . $studentName . '%');
                });
            }

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            $contributions = $query->get();

            // If no contributions found, return a fail response
            if ($contributions->isEmpty()) {
                $response = new ResponseModel(
                    'No contributions found.',
                    false,
                    null
                );
                return response()->json($response);
            }


            $data = $contributions;


            $response = new ResponseModel(
                (new Messages())->success,
                true,
                $data
            );

            return response()->json($response);
        } catch (\Exception $e) {

            $response = new ResponseModel(
                (new Messages())->fail,
                false,
                null
            );
            return response()->json($response);
        }
    }

    // Method for Downloading Selected Contributions as ZIP
    public function downloadZip(Request $request) {
        try {
            // Get the list of selected contribution IDs from the request
            $selectedContributions = $request->input('contribution_ids', []);

            if (empty($selectedContributions)) {
                // Return error if no contributions selected
                $response = new ResponseModel(
                    'No contributions selected.',
                    false,
                    null
                );
                return response()->json($response);
            }

            // Fetch the contributions by IDs
            $contributions = Contribution::whereIn('id', $selectedContributions)->get();

            if ($contributions->isEmpty()) {
                // Return error if no contributions are found for the selected IDs
                $response = new ResponseModel(
                    'No valid contributions found.',
                    false,
                    null
                );
                return response()->json($response);
            }

            // Create a temporary directory to store the files
            $tempDir = storage_path('app/temp/contributions/');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            // Add each contribution file to the temporary directory
            foreach ($contributions as $contribution) {
                $fileName = $contribution->article_filename;
                $filePath = storage_path('app/public/articles/' . $fileName);

                // Copy the file to the temporary directory
                if (file_exists($filePath)) {
                    copy($filePath, $tempDir . $fileName);
                }

                if ($contribution->image_filename) {
                    $imagePath = storage_path('app/public/images/' . $contribution->image_filename);
                    if (file_exists($imagePath)) {
                        copy($imagePath, $tempDir . $contribution->image_filename);
                    }
                }
            }

            // Create a ZIP file from the temporary directory
            $zipFileName = 'contributions.zip';
            $zip = new ZipArchive();
            $zipPath = storage_path('app/temp/' . $zipFileName);
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $files = glob($tempDir . '*');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));  // Add file to zip
                }
                $zip->close();
            }

            // Return the ZIP file for download
            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                (new Messages())->fail,
                false,
                null
            );
            return response()->json($response);
        }
    }

    // Method for Statistics and Reports
    public function statisticsAndReports() {
        try {
            // Number of contributions per faculty
            $contributionsPerFaculty = Contribution::selectRaw('faculty, COUNT(*) as contribution_count')
                ->groupBy('faculty')
                ->get();

            // Percentage of contributions by faculty
            $totalContributions = Contribution::count();
            $contributionsPercentage = $contributionsPerFaculty->map(function ($item) use ($totalContributions) {
                $item->percentage = ($item->contribution_count / $totalContributions) * 100;
                return $item;
            });

            // Contributors count per academic year
            $contributorsCount = Contribution::selectRaw('YEAR(created_at) as year, COUNT(DISTINCT student_id) as contributors_count')
                ->groupBy('year')
                ->get();

            // Unreviewed contributions (contributions that haven't been commented on after 14 days)
            $unreviewedContributions = Contribution::where('status', 'pending')
                ->whereDate('created_at', '<', now()->subDays(14))
                ->get();

            // Prepare response data
            $data = [
                'contributions_per_faculty' => $contributionsPercentage,
                'contributors_count' => $contributorsCount,
                'unreviewed_contributions' => $unreviewedContributions
            ];

            // Return response with statistics data
            $response = new ResponseModel(
                (new Messages())->success,
                true,
                $data
            );

            return response()->json($response);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                (new Messages())->fail,
                false,
                null
            );
            return response()->json($response);
        }
    }
}
