<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Contribution;
use App\Models\Faculty;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{

    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (! Auth::attempt([
                'email'    => $data['email'],
                'password' => $data['password'],
            ])) {
                $response = new ResponseModel(
                    'Invalid email or password',
                    1,
                    null
                );
                return response()->json($response);
            }

            $user = Auth::user();

            $token = $user->createToken('user-token', ['user'])->plainTextToken;

            $userDetails = [
                'id'                 => $user->id,
                'first_name'         => $user->first_name,
                'last_name'          => $user->last_name,
                'email'              => $user->email,
                'profile'            => $user->profile,
                'is_password_change' => $user->is_password_change,
                'last_login_at'      => $user->last_login_at,
                'last_login_ip'      => $user->last_login_ip,
                'role_id'            => $user->role_id,
                'faculty_id'         => $user->faculty_id,
                'role_name'          => optional($user->role)->name ?? "Unknown Role",
                'faculty_name'       => optional($user->faculty)->name ?? null,
            ];

            $roles = [
                1 => "admin",
                2 => "manager",
                3 => "coordinator",
                4 => "student",
                5 => "guest",
            ];
            $currentDateTime = Carbon::now();
            $user->update([
                'last_login_at' => $currentDateTime,
            ]);

            $response = new ResponseModel(
                'Login successful',
                0,
                [
                    'user'  => $userDetails,
                    'token' => $token,
                    'role'  => $roles[$user->role_id] ?? "unknown",
                ]
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function passwordUpdate(Request $request)
    {
        try {
            // Validate request data
            $data = $request->validate([
                'user_id'          => 'required|exists:users,id',
                'old_password'     => 'required|string|min:8',
                'new_password'     => 'required|string|min:8',
                'confirm_password' => 'required|string|same:new_password',
                'updateby'         => 'required|exists:users,id',
            ], [
                'old_password.required'     => 'Old password is required.',
                'new_password.required'     => 'New password is required.',
                'new_password.min'          => 'New password must be at least 6 characters.',
                'confirm_password.required' => 'Confirm password is required.',
                'confirm_password.same'     => 'Confirm password must match the new password.',
                'updateby.required'         => 'User ID is required.',
                'updateby.exists'           => 'User not found.',
            ]);

            // Get user by ID
            $user = User::find($data['user_id']);

            if (! $user) {
                $response = new ResponseModel(
                    'User not found.',
                    1,
                    null
                );

                return response()->json($response, 200);
            }

            // Check if old password is correct
            if (! Hash::check($data['old_password'], $user->password)) {
                $response = new ResponseModel(
                    'Old password is incorrect.',
                    1,
                    null
                );

                return response()->json($response, 200);
            }

            // Update password
            $user->update([
                'is_password_change' => true,
                'password'           => bcrypt($data['new_password']),
            ]);

            $response = new ResponseModel(
                'success',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            $response = new ResponseModel(
                'Logout successful',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function countData()
    {
        if (Auth::user()->role_id != 1) {
            return $this->unauthorizedResponse();
        }

        // Optimized counting logic for Users based on role
        $roleCounts = $this->countUserRoles();

        // Past month statistics
        $oneMonthAgo = Carbon::now()->subMonth();

        $contributions = $this->fetchContributionsWithComments();

        $reports = $this->analyzeContributionComments($contributions);

        $contributionsWithoutComment = $this->getContributionsWithoutComments($reports);
        $contributionsWithoutCommentAfter14 = $this->getContributionsWithoutRecentComments($reports);

        // Response structure
        $data = [
            'students'                  => $roleCounts['students'],
            'coordinators'              => $roleCounts['coordinators'],
            'managers'                  => $roleCounts['managers'],
            'guests'                    => $roleCounts['guests'],
            'faculties'                 => Faculty::count(),
            'contributions'             => Contribution::count(),
            'approved'                  => Contribution::where('status', 'selected')->count(),
            'rejected'                  => Contribution::where('status', 'rejected')->count(),
            'setting'                   => SystemSetting::first(),
            // 'past_month'                => $this->getMonthlyStats($oneMonthAgo),
            'contributionData'          => $this->calculateMonthlyContributions(),
            'contributionDataByFaculty' => $this->getContributionDataByFaculty(),
            'contributionWithoutComment' => $contributionsWithoutComment,
            'contributionWithoutCommentAfter14' =>  $contributionsWithoutCommentAfter14,
        ];

        return response()->json(new ResponseModel('success', 0, $data));
    }

    public function ContributionsByFaultyEachYear(): array
    {
        $contributions = Contribution::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $result = [];

        foreach ($contributions as $item) {
            $year = $item->year;
            $month = $item->month;
            $total = $item->total;

            $monthName = match ($month) {
                1  => "Jan",
                2  => "Feb",
                3  => "Mar",
                4  => "Apr",
                5  => "May",
                6  => "Jun",
                7  => "Jul",
                8  => "Aug",
                9  => "Sep",
                10 => "Oct",
                11 => "Nov",
                12 => "Dec",
            };

            $result[] = [
                'year'  => $year,
                'name'  => $monthName,
                'value' => $total,
            ];
        }
        return $result;
    }

    public function calculateMonthlyContributions(): array
    {
        // Get contributions grouped by month with counts
        $contributions = Contribution::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        // Initialize array for all 12 months
        $result     = [];
        $monthNames = [
            1  => "Jan",
            2  => "Feb",
            3  => "Mar",
            4  => "Apr",
            5  => "May",
            6  => "Jun",
            7  => "Jul",
            8  => "Aug",
            9  => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec",
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
        // Fetch contributions grouped by faculty, year, and user
        $contributions = Contribution::selectRaw('faculty_id, YEAR(created_at) as year, user_id')
            ->get();

        // Step 1: Total contributions per year
        $totalPerYear = $contributions
            ->groupBy('year')
            ->map(fn($items) => $items->count());

        // Step 2: Build faculty-year-counts
        $facultyYearCounts = [];
        $facultyYearContributors = [];

        foreach ($contributions as $item) {
            $facultyId = $item->faculty_id;
            $year = $item->year;
            $userId = $item->user_id;

            // Count contributions
            if (!isset($facultyYearCounts[$facultyId][$year])) {
                $facultyYearCounts[$facultyId][$year] = 0;
            }
            $facultyYearCounts[$facultyId][$year]++;

            // Count contributors (unique user IDs)
            $facultyYearContributors[$facultyId][$year][$userId] = true;
        }

        // Step 3: Fetch faculties
        $faculties = Faculty::all();

        // Step 4: Map result with percentage and contributor count
        $result = [];

        foreach ($faculties as $faculty) {
            foreach ([2023, 2024, 2025] as $year) {
                $value = $facultyYearCounts[$faculty->id][$year] ?? 0;
                $yearTotal = $totalPerYear[$year] ?? 0;
                $contributorIds = $facultyYearContributors[$faculty->id][$year] ?? [];
                $contributors = count($contributorIds);

                $percentage = $yearTotal > 0 ? round(($value / $yearTotal) * 100, 2) : 0;

                $result[] = [
                    'faculty'      => $faculty->name,
                    'year'         => $year,
                    'value'        => $value,
                    'percentage'   => $percentage,
                    'contributors' => $contributors,
                ];
            }
        }
        return $result;
    }

    private function fetchContributionsWithComments()
    {
        return Contribution::with(['comments', 'faculty'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function analyzeContributionComments($contributions)
    {
        $currentDate = Carbon::now();
        $cutoffDate  = $currentDate->copy()->subDays(14);

        return $contributions->map(function ($contribution) use ($currentDate, $cutoffDate) {
            $hasComments      = $contribution->comments->isNotEmpty();
            $hasRecentComment = $contribution->comments->contains(function ($comment) use ($cutoffDate) {
                return $comment->created_at >= $cutoffDate;
            });

            $status = 'normal';
            if (! $hasComments) {
                $status = 'no_comments';
            } elseif (! $hasRecentComment && $contribution->created_at <= $cutoffDate) {
                $status = 'no_comments_after_14_days';
            }

            return [
                'id'          => $contribution->id,
                'title'       => $contribution->title,
                'description' => $contribution->description,
                'faculty'     => $contribution->faculty->name ?? 'Unknown',
                'contributor' => $contribution->student->email ?? 'UnKnown',
                'created_at'  => $contribution->created_at->format('Y-m-d H:i:s'),
                'status'      => $status,
                'created_at'  => $contribution->created_at,
            ];
        });
    }

    private function getContributionsWithoutComments($reports)
    {
        return $reports->where('status', 'no_comments')->values();
    }

    private function getContributionsWithoutRecentComments($reports)
    {
        return $reports->where('status', 'no_comments_after_14_days')->values();
    }

    private function unauthorizedResponse()
    {
        return response()->json(new ResponseModel('Unauthorized', 1, null));
    }

    private function countUserRoles()
    {
        return [
            'students'     => User::where('role_id', 4)->count(),
            'coordinators' => User::where('role_id', 3)->count(),
            'managers'     => User::where('role_id', 2)->count(),
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

    public function getContributionsByYear(Request $request)
    {
        try {
            $year = $request->input('year', Carbon::now()->year);

            $contributions = Contribution::with(['faculty', 'student'])
                ->whereYear('created_at', $year)
                ->get();

            $grouped = $contributions->groupBy('faculty.name');

            $result = $grouped->map(function ($items, $facultyName) use ($contributions) {
                $studentCount = $items->groupBy('user_id')->count();

                return [
                    'faculty'                 => $facultyName,
                    'contribution_count'      => $items->count(),
                    'student_count'           => $studentCount,
                    'contribution_percentage' => $contributions->count() > 0
                        ? round(($items->count() / $contributions->count()) * 100, 2)
                        : 0,
                ];
            })->values();

            return response()->json(new ResponseModel('success', 0, $result));
        } catch (\Exception $e) {
            return response()->json(new ResponseModel($e->getMessage(), 2, null));
        }
    }

    public function getCommentExceptionReports(Request $request)
    {
        try {
            $currentDate = Carbon::now();
            $cutoffDate  = $currentDate->copy()->subDays(14);

            $contributions = Contribution::with(['comments', 'faculty'])
                ->orderBy('created_at', 'desc')
                ->get();

            $reports = $contributions->map(function ($contribution) use ($currentDate, $cutoffDate) {
                $hasComments      = $contribution->comments->isNotEmpty();
                $hasRecentComment = $contribution->comments->contains('created_at', '>=', $cutoffDate);

                $status = 'normal';
                if (! $hasComments) {
                    $status = 'no_comments';
                } elseif (! $hasRecentComment && $contribution->created_at <= $cutoffDate) {
                    $status = 'no_comments_after_14_days';
                }

                return [
                    'contribution_id'     => $contribution->id,
                    'title'               => $contribution->title,
                    'faculty'             => $contribution->faculty->name ?? 'Unknown',
                    'created_at'          => $contribution->created_at->format('Y-m-d H:i:s'),
                    'days_since_creation' => $contribution->created_at->diffInDays($currentDate),
                    'status'              => $status,
                ];
            });

            $exceptions = $reports->filter(function ($report) {
                return $report['status'] !== 'normal';
            })->values();

            return response()->json(new ResponseModel(
                'success',
                0,
                [
                    'total_contributions'                   => $contributions->count(),
                    'contributions_without_comments'        => $reports->where('status', 'no_comments')->count(),
                    'contributions_without_recent_comments' => $reports->where('status', 'no_comments_after_14_days')->count(),
                    'exception_reports'                     => $exceptions,
                ]
            ));
        } catch (\Exception $e) {
            return response()->json(new ResponseModel($e->getMessage(), 2, null));
        }
    }
}
