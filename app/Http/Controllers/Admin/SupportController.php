<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Configuration;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupportController extends Controller
{
    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function sendMessages(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->authorize('manageSupport', Campaign::class);

        $this->validate($request, [
            'user_ids' => 'required|array',
            'message' => 'required',
        ]);


        $recipients = $request->get('recipients');
        $message = $request->get('message');
        $useSms = $request->get('useSms') ?: true;
        $usePush = $request->get('usePush') ?: false;

        if(blank($message)){
            return response()->json(ApiResponse::failedResponse('Message field required'));
        }

        /// save in the campaigns  / notifications table

        $recipientIds = collect($recipients)->pluck('id');

        Log::info('recipientIds: ' . json_encode($recipientIds));

        $campaign = Campaign::create([
            'message' => $message,
            'use_sms' => $useSms == 'true',
            'use_push' => $usePush == 'true',
            'status' => 'pending',
            'total' => count($recipientIds),
            'author' => $request->user()->{'id'}
        ]);

        $dataToInsert = [];
        foreach ($recipientIds as $id){

            $user = User::find($id);
            if(blank($user)){
                continue;
            }

            $formattedMessage = replaceFormUserTags($message, $user);

            $dataToInsert[] = [
                'campaign_id' => $campaign->id,
                'user_id' => $id,
                'message' => $formattedMessage,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Message::with([])->insert($dataToInsert);

        return response()->json(ApiResponse::successResponseWithMessage());
    }

    public function getLastNWeeksCampaign($weeks = 4): \Illuminate\Http\JsonResponse
    {
        $campaigns = Campaign::with(['messages'])->orderByDesc('created_at')
            ->where('created_at', '>', Carbon::now()->subWeeks($weeks))
            ->get();
        collect($campaigns)->map(function ($item) {
            $item->createdAt = Carbon::parse($item->{'created_at'})->toDayDateTimeString();
        });
        return response()->json(ApiResponse::successResponseWithData($campaigns));
    }
}
