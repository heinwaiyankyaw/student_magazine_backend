<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Http\Helpers\TransactionLogger;
use App\Models\Notification;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemSettingController extends Controller
{
    public function index()
    {
        $setting = SystemSetting::where('active_flag', 1)->first();

        $response = new ResponseModel(
            'success',
            0,
            $setting
        );

        return response()->json($response, 200);
    }

    public function update(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'id' => 'required|exists:system_settings,id',
                'academic_year' => 'required|string|max:20|min:3',
                'closure_date' => 'required',
                'final_closure_date' => 'required',
            ]);

            $setting = SystemSetting::findOrFail($data['id']);

            $user = Auth::user();
            // Update the employee record
            $setting->update([
                'academic_year' => $data['academic_year'],
                'closure_date' => Carbon::parse($data['closure_date'])->format('Y-m-d'),
                'final_closure_date' => Carbon::parse($data['final_closure_date'])->format('Y-m-d'),
                'updateby' => $user->id,
            ]);

            $notification = Notification::create([
                'title' => 'System Setting Updated!',
                'message' => "Academic Year: {$setting->academic_year}, Submission Closure Date: {$setting->closure_date}, Final Closure Date: {$setting->final_closure_date}",
                'createby' => $user->id,
            ]);

            TransactionLogger::log('system-setting', 'update', true, "Update System Setting'");

            for ($i = 2; $i <= 5; $i++) {
                // Attach notification 
                $notification->roles()->attach($i, [
                    'is_read' => false,
                    'createby' => $user->id,
                ]);
            }

            // Prepare the response
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
}
