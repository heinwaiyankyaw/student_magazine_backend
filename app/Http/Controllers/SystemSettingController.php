<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index() {
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
            

                // Update the employee record
                $setting->update([
                    'academic_year' => $data['academic_year'],
                    'closure_date' => Carbon::parse($data['closure_date'])->format('Y-m-d'),
                    'final_closure_date' => Carbon::parse($data['final_closure_date'])->format('Y-m-d'),
                    'updateby' => auth()->id(),
                ]);
                

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
