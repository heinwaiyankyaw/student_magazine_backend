<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\RoleNotification;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function getNotifications(){
        try{
            $user = Auth::user();

            // Get notifications related with this user
            $userNotifications = $user->notifications->map(function ($notification) {
                $notification->type = 'user'; // Set type to 'user' for user-specific notifications
                return $notification;
            });

            // Get notifications related with this user's role
            $roleNotifications = $user->roleNotifications->map(function ($notification) {
                $notification->type = 'role'; // Set type to 'role' for role-based notifications
                return $notification;
            });

            // Merge both collections and remove duplicates
            $allNotifications = $userNotifications->merge($roleNotifications)->unique('id');

            // get notifications
            $notifications = $allNotifications->map(function ($notification) {

                $pivotData = $notification->pivot ?? null;
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at,
                    'active_flag' => $notification->active_flag,
                    'is_read' => $pivotData ? $pivotData->is_read : false,
                    'type' => $notification->type,
                ];
            });

            // If there is no notification
            if ($notifications->isEmpty()) {
                $response = new ResponseModel(
                    'No notifications found.',
                    0,
                    null);
                return response()->json($response);
            }

            // Prepare response
            $response = new ResponseModel(
                'success',
                0,
                $notifications);

            return response()->json($response, 200);
        }   catch(\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }

    public function markAsRead(Request $request){
        try{
            $request->validate([
                'notification_id' => 'required|integer',
                'type' => 'required|in:user,role'
            ]);

            $user = Auth::user();

            DB::enableQueryLog();

            if ($request->type === 'user') {
                $notification = UserNotification::where('user_id', $user->id)
                    ->where('notification_id', $request->notification_id)
                    ->first();
            } else {
                $notification = RoleNotification::whereIn('role_id', $user->roles->pluck('id'))
                    ->where('notification_id', $request->notification_id)
                    ->first();
            }

            if ($notification) {
                Log::info('Notification to update:', [$notification]);
                $notification->is_read = true;
                $notification->save();
                $queryLog = DB::getQueryLog();
                Log::info('Executed query:', $queryLog);

                // Prepare response
                $response = new ResponseModel(
                    'Marked As Read.',
                    0,
                    null
                );

                return response()->json($response, 200);
            }else {
                // If notification not found
                $response = new ResponseModel(
                    'Notification not found.',
                    1,
                    null
                );
                return response()->json($response, 404);
            }
        } catch(\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }
}
