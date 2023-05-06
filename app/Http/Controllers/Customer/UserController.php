<?php

namespace App\Http\Controllers\Customer;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\LocationLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function submitAgreementStatus(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'status' => 'required|bool'
        ]);

        $status = $request->get('status');

        $user = $request->user();
        $user->customer()->update([
            'agreed_to_terms_or_service' => $status
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());
    }

    /**
     * @throws ValidationException
     */
    public function submitCallLogs(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'logs' => 'required'
        ]);


        $logs = $request->get('logs');
        if(is_string($logs)){
            $logs = json_decode($logs, true);
        }

        $user = $request->user();

        $upsertData = [];

        foreach ($logs as $log) {
            $upsertData[] = [
                'user_id' => $user->id,
                'timestamp' => Carbon::parse($log['timestamp']),
                'name' => $log['name'],
                'phone' => $log['phone'],
                'duration' => $log['duration'],
                'call_type' => $log['call_type']
            ];
        }

        CallLog::with([])->upsert($upsertData, ['user_id','timestamp','phone'], []);

        return response()->json(ApiResponse::successResponseWithMessage());
    }

    /**
     * @throws ValidationException
     */
    public function submitLocation(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'lat' => 'required',
            'long' => 'required',
        ]);

        $lat = $request->get('lat');
        $long = $request->get('long');

        $user = $request->user();

        // there should be at least 5 mins interval if the user has not moved
        $lastSameLocation = LocationLog::with([])->where([
            'lat' => $lat,
            'long' => $long
        ])->orderByDesc('created_at')->first();


        if($lastSameLocation) {

            LocationLog::with([])->update(
                [
                    'user_id' => $user->id,
                    'lat' => $lat,
                    'long' => $long,
                    'frequency' => $lastSameLocation->{'frequency'} + 1
                ],
            );

        }else {
            LocationLog::with([])->create([
                'user_id' => $user->id,
                'lat' => $lat,
                'long' => $long
            ]);
        }


        return response()->json(ApiResponse::successResponseWithMessage());
    }


}
