<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\Messages;
use App\Models\Faculty;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Storage;

class MarketingManagerController extends Controller
{
    // Dashboard statistics
    public function index()
    {
        try {
            if (Auth::user()->role_id != 2) {
                return $this->unauthorizedResponse();
            }

            // Role-based user count (students, coordinators, etc.)
            $roleCounts = $this->countUserRoles();

            // Get data for past month
            $oneMonthAgo = Carbon::now()->subMonth();

            // Prepare all response data
            $data = [
                'students'                    => $roleCounts['students'],
                'coordinators'               => $roleCounts['coordinators'],
                'faculties'                  => Faculty::count(),
                'contributions'              => Contribution::count(),
                'approved'                   => Contribution::where('status', 'selected')->count(),
                'pending'                    => Contribution::where('status', 'pending')->count(),
                'reviewed'                   => Contribution::where('status', 'reviewed')->count(),
                'unreviewed'                 => Contribution::doesntHave('comments')->count(),
                'rejected'                   => Contribution::where('status', 'rejected')->count(),
                'setting'                    => SystemSetting::first() ?? [],
                'past_month'                 => $this->getMonthlyStats($oneMonthAgo),
                'contributionData'           => $this->calculateMonthlyContributions(),
                'contributionDataByFaculty'  => $this->getContributionDataByFaculty(),
            ];

            return response()->json(new ResponseModel(
                "success",
                0,
                $data
            ));
        } catch (\Exception $e) {
            //Log::error('Dashboard index error: ' . $e->getMessage());

            return response()->json(new ResponseModel(
                $e->getMessage(),
                2,
                null
            ), 500);
        }
    }

    public function selectedArticles()
    {
        $articles = Contribution::where('active_flag', 1)->where('status', 'selected')->with(['faculty:id,name', 'student:id,first_name,last_name'])->latest()->get();
        $response = new ResponseModel(
            'success',
            0,
            $articles
        );
        return response()->json($response);
    }

    // View all approved contributions
    public function viewContributions(Request $request)
    {
        try {
            // Get filter criteria from the request
            $studentName = $request->input('student_name', null);
            $date = $request->input('date', null);

            // Query approved contributions, filter by student name or date
            $query = Contribution::where('status', 'approved');

            if ($studentName) {
                $query->whereHas('student', function ($q) use ($studentName) {
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
    public function downloadZip(Request $request)
    {
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
    public function statisticsAndReports()
    {
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

    public function calculateMonthlyContributions(): array
    {
        // Get contributions grouped by month with counts
        $contributions = Contribution::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        // Initialize array for all 12 months
        $result = [];
        $monthNames = [
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec"
        ];

        foreach ($monthNames as $num => $name) {
            $result[] = [
                'name'  => $name,
                'value' => $contributions[$num] ?? 0,
            ];
        }

        return $result;
    }

    private function getContributionDataByFaculty()
    {
        return Faculty::withCount(['contributions'])->get()->map(function ($faculty) {
            return [
                'name' => $faculty->name,
                'value' => $faculty->contributions_count,
            ];
        });
    }

    private function unauthorizedResponse()
    {
        return response()->json(new ResponseModel('Unauthorized', 1, null));
    }

    private function countUserRoles()
    {
        return [
            'coordinators' => User::where('role_id', 3)->count(),
            'students'     => User::where('role_id', 4)->count(),
            'guests'       => User::where('role_id', 5)->count(),
        ];
    }

    private function getMonthlyStats($oneMonthAgo)
    {
        return [
            'total_submissions' => Contribution::where('created_at', '>=', $oneMonthAgo)->count(),
            'pending_reviews'   => Contribution::where('created_at', '>=', $oneMonthAgo)->where('status', 'pending')->count(),
            'approved'          => Contribution::where('created_at', '>=', $oneMonthAgo)->where('status', 'selected')->count(),
        ];
    }

    private function getFacultyContributions()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return Faculty::select('name')->withCount(['contributions' => function ($query) use ($oneMonthAgo) {
            $query->where('created_at', '>=', $oneMonthAgo);
        }])
            ->having('contributions_count', '>', 0)
            ->get();
    }
}
