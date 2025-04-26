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

    // public function countData()
    // {
    //     $students       = User::where('role_id', 4)->count();
    //     $coordinators   = User::where('role_id', 3)->count();
    //     $managers       = User::where('role_id', 2)->count();
    //     $guests         = User::where('role_id', 5)->count();
    //     $constributions = Contribution::count();
    //     $faculties      = Faculty::count();

    //     $oneMonthAgo = Carbon::now()->subMonth();

    //     // Total submissions in the past month
    //     $totalSubmissions = Contribution::where('created_at', '>=', $oneMonthAgo)->count();

    //     // Pending reviews in the past month
    //     $pendingReviews = Contribution::where('created_at', '>=', $oneMonthAgo)
    //         ->where('status', 'pending')
    //         ->count();

    //     // Approved (selected) in the past month
    //     $approvedSubmissions = Contribution::where('created_at', '>=', $oneMonthAgo)
    //         ->where('status', 'selected')
    //         ->count();

    //     $faculties = Faculty::withCount(['contributions' => function ($query) use ($oneMonthAgo) {
    //         $query->where('created_at', '>=', $oneMonthAgo);
    //     }])
    //         ->having('contributions_count', '>', 0)
    //         ->get();

    //     $donurtChart = [
    //         'labels' => $faculties->pluck('name'),
    //         'data'   => $faculties->pluck('contributions_count'),
    //     ];

    //     $monthlyCounts = $this->calculateMonthlyContributions();

    //     $barChart = [
    //         'labels' => $monthlyCounts->pluck('month'),
    //         'data'   => $monthlyCounts->pluck('total'),
    //     ];

    //     if (Auth::user()->role_id != 1) {
    //         $response = new ResponseModel(
    //             'Unauthorized',
    //             1,
    //             null
    //         );
    //         return response()->json($response);
    //     }

    //     $data = [
    //         'students'       => $students,
    //         'coordinators'   => $coordinators,
    //         'managers'       => $managers,
    //         'guests'         => $guests,
    //         'constributions' => $constributions,
    //         'faculties'      => $faculties,
    //         'past_month'     => [
    //             'total_submissions' => $totalSubmissions,
    //             'pending_reviews'   => $pendingReviews,
    //             'approved'          => $approvedSubmissions,
    //         ],
    //         'donut_chart'    => [
    //             $donurtChart,
    //         ],
    //         'bar_chart'      => [
    //             $barChart,
    //         ],
    //     ];
    //     $response = new ResponseModel(
    //         'success',
    //         0,
    //         $data
    //     );
    //     return response()->json($response);
    // }

    // private function calculateMonthlyContributions()
    // {
    //     $startDate = Carbon::now()->subMonths(11)->startOfMonth();
    //     $endDate   = Carbon::now()->endOfMonth();

    //     $contributions = Contribution::whereBetween('created_at', [$startDate, $endDate])->get();

    //     return collect(range(0, 11))->map(function ($i) use ($contributions) {
    //         $date      = Carbon::now()->subMonths(11 - $i);
    //         $monthName = $date->format('M');
    //         $count     = $contributions->filter(function ($item) use ($date) {
    //             return $item->created_at->format('Y-m') === $date->format('Y-m');
    //         })->count();

    //         return [
    //             'month' => $monthName,
    //             'total' => $count,
    //         ];
    //     });
    // }

    public function countData()
    {
        if (Auth::user()->role_id != 1) {
            return $this->unauthorizedResponse();
        }

        // Optimized counting logic for Users based on role
        $roleCounts = $this->countUserRoles();

        // Past month statistics
        $oneMonthAgo = Carbon::now()->subMonth();

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
            'past_month'                => $this->getMonthlyStats($oneMonthAgo),
            'contributionData'          => $this->calculateMonthlyContributions(),
            'contributionDataByFaculty' => $this->getContributionDataByFaculty(),
        ];

        return response()->json(new ResponseModel('success', 0, $data));
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
        return Faculty::withCount(['contributions'])->get()->map(function ($faculty) {
            return [
                'name'  => $faculty->name,
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

    // private function calculateMonthlyContributions()
    // {
    //     $year      = Carbon::now()->year;
    //     $startDate = Carbon::create($year, 1, 1)->startOfMonth();
    //     $endDate   = Carbon::create($year, 12, 31)->endOfMonth();

    //     $contributions = Contribution::whereBetween('created_at', [$startDate, $endDate])->get();

    //     return collect(range(1, 12))->map(function ($month) use ($year, $contributions) {
    //         $monthDate = Carbon::create($year, $month, 1);
    //         $monthName = $monthDate->format('M');

    //         $count = $contributions->filter(function ($item) use ($monthDate) {
    //             return $item->created_at->format('Y-m') === $monthDate->format('Y-m');
    //         })->count();

    //         return [
    //             'month' => $monthName,
    //             'total' => $count,
    //         ];
    //     });
    // }

    // public function getContributionsByYear(Request $request)
    // {
    //     try {
    //         $year = $request->input('year', Carbon::now()->year);

    //         $contributions = Contribution::whereYear('created_at', $year)
    //             ->get();

    //         return response()->json(new ResponseModel('success', 0, $contributions));
    //     } catch (\Exception $e) {
    //         return response()->json(new ResponseModel($e->getMessage(), 2, null));
    //     }
    // }
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