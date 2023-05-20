<?php

namespace App\Channels;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{

    /**
     * Send the NOTIFICATION VIA ICSMS
     *
     * @param  mixed $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $arrayPhoneMessage = $notification->toSMS($notifiable);

        Log::info('sending sms ....: ' . json_encode($arrayPhoneMessage));

        if(!$arrayPhoneMessage){
            Log::info('invalid arrayPhoneMessage parameter');
            return;
        }

        if(!isset($arrayPhoneMessage['phone']) || !isset($arrayPhoneMessage['message'])){
            Log::info('phone field is not set OR message field is not set');
            return;
        }

        $phone = $arrayPhoneMessage['phone'];
        $message = $arrayPhoneMessage['message'];

        $apiToken = config('custom.sms_api_token');
        $apiUrl= config('custom.sms_api_url');

        Log::info(sprintf('url: %s , apiToken: %s', $apiUrl, $apiToken));

        $response = Http::withToken($apiToken)->post($apiUrl, [
            "supid" => 6,
            "username" => "Eazisend",
            "sourceAddress" => "Eazisend",
            "shortMessage" => $message,
            "destinationAddress" => $phone,
            "messageDescription" => "personal",
            "messageType" => "quick sms",
            "randomspecial" => "",
            "specialUnicodeSupport" => true
        ]);

        if($response->failed()){
            Log::info('request failed: ' . $response->reason());
            return;
        }

        $responseBody = $response->body(); // returns a string
        Log::info('sms http response: ' . $responseBody);

    }

//    /**
//     * Send the NOTIFICATION VIA WIREPICK
//     *
//     * @param  mixed $notifiable
//     * @param Notification $notification
//     * @return void
//     */
//    public function send(mixed $notifiable, Notification $notification)
//    {
//        $arrayPhoneMessage = $notification->toSMS($notifiable);
//
//        Log::info('sending sms ....: ' . json_encode($arrayPhoneMessage));
//
//        if(!$arrayPhoneMessage){
//            Log::info('invalid arrayPhoneMessage parameter');
//            return;
//        }
//
//        if(!isset($arrayPhoneMessage['phone']) || !isset($arrayPhoneMessage['message'])){
//            Log::info('phone field is not set OR message field is not set');
//            return;
//        }
//
//        $phone = $arrayPhoneMessage['phone'];
//        $message = $arrayPhoneMessage['message'];
//
//        WirePickProvider::send($phone, trimMessage($message),'ICGC LT');
//
//    }


//    /**
//     * Send the NOTIFICATION VIA HUBTEL
//     *
//     * @param  mixed $notifiable
//     * @param Notification $notification
//     * @return void
//     */
//    public function send(mixed $notifiable, Notification $notification)
//    {
//        $arrayPhoneMessage = $notification->toSMS($notifiable);
//
//        Log::info('sending sms ....: ' . json_encode($arrayPhoneMessage));
//
//        if(!$arrayPhoneMessage){
//            Log::info('invalid arrayPhoneMessage parameter');
//            return;
//        }
//
//        if(!isset($arrayPhoneMessage['phone']) || !isset($arrayPhoneMessage['message'])){
//            Log::info('phone field is not set OR message field is not set');
//            return;
//        }
//
//        $phone = $arrayPhoneMessage['phone'];
//        $message = $arrayPhoneMessage['message'];
//
//        /**
//         * Requires libcurl
//         */
//
//        $query = array(
//            "clientid" => "string",
//            "clientsecret" => "string",
//            "from" => "ICGC LT",
//            "to" => $phone,
//            "content" => $message
//        );
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, [
//            CURLOPT_URL => "https://devp-sms03726-api.hubtel.com/v1/messages/send?" . http_build_query($query),
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_CUSTOMREQUEST => "GET",
//        ]);
//
//        $response = curl_exec($curl);
//        $error = curl_error($curl);
//
//        curl_close($curl);
//
//        if ($error) {
//            Log::error( "cURL Error #:" . json_encode($error));
//        } else {
//            Log::error( "cURL Response #:" . json_encode($response));
//        }
//
//    }

//    /**
//     * Send the NOTIFICATION VIA VONAGE
//     *
//     * @param  mixed $notifiable
//     * @param Notification $notification
//     * @return void
//     */
//    public function send(mixed $notifiable, Notification $notification)
//    {
//        $arrayPhoneMessage = $notification->toSMS($notifiable);
//
//        Log::info('sending sms ....: ' . json_encode($arrayPhoneMessage));
//
//        if(!$arrayPhoneMessage){
//            Log::info('invalid arrayPhoneMessage parameter');
//            return;
//        }
//
//        if(!isset($arrayPhoneMessage['phone']) || !isset($arrayPhoneMessage['message'])){
//            Log::info('phone field is not set OR message field is not set');
//            return;
//        }
//
//        $phone = $arrayPhoneMessage['phone'];
//        $message = $arrayPhoneMessage['message'];
//
//        try{
//
//            $appName = 'ICGC LT';
//
//            Log::info('live / dev environment ... ');
//
//            $basic  = new \Vonage\Client\Credentials\Basic('d5884851', 'ZuYMv4D2SvrdzcFg');
////               $basic  = new \Vonage\Client\Credentials\Basic(config(''), config(''));
//            $client = new \Vonage\Client($basic);
//
//            $response = $client->sms()->send(
//                new \Vonage\SMS\Message\SMS($phone, $appName, $message)
//            );
//
//            $message = $response->current();
//
//            if ($message->getStatus() == 0) {
//                Log::info('The message was sent successfully');
//            } else {
//                Log::info("The message failed with status: " . $message->getStatus());
//            }
//
//        }catch (\Exception $exception){
//            Log::info("sms error: " . $exception->getMessage());
//        }
//
//    }

}
