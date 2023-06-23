<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignCreated;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Log;

class SendBulkMessages extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-bulk-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campaigns = Campaign::with([])->where('status', 'pending')->get();
        Log::info('campaigns: ' . json_encode($campaigns));
        foreach ($campaigns as $campaign){
            $campaign->update([
                'status' => 'processing'
            ]);
            foreach ($campaign->messages as $message){

                $pushNotification = null;
                if($campaign->use_push){
//                    $pushNotification = $this->sendPushNotification($n->driver_user_id, 'Notice', $n->message, 'campaign');
                }

                $user = User::with([])->find($message->{'user_id'});
                if(!$user){
                    continue;
                }


                $user->notify(new CampaignCreated(campaign: $campaign));

//                getSuperAdmin()->notify(new DriverNotificationRequested($n->message, $user->phone, $c->use_sms, $c->use_push, $pushNotification));

            }

            $campaign->update([
                'status' => 'processed'
            ]);
        }
    }
}
