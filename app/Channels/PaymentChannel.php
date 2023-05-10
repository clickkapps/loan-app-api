<?php

namespace App\Channels;

use App\Models\Payment;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        $payment = $notification->toPaymentGateway($notifiable);

        $callbackUrl = config('custom.payment.callbackUrl');
        $cancellationUrl = config('custom.payment.cancellationUrl');
        $returnUrl = config('custom.payment.returnUrl');
        $logo = config('custom.payment.logo');


//        $apiUrl= config('custom.sms_api_url');
//
//        Log::info(sprintf('url: %s , apiToken: %s', $apiUrl, $apiToken));
//
//        $response = Http::withToken($apiToken)->post($apiUrl, [
//            "supid" => 6,
//            "username" => "Eazisend",
//            "sourceAddress" => "Eazisend",
//            "shortMessage" => $message,
//            "destinationAddress" => $phone,
//            "messageDescription" => "tithe",
//            "messageType" => "quick sms",
//            "randomspecial" => "",
//            "specialUnicodeSupport" => true
//        ]);
//
//        if($response->failed()){
//            Log::info('request failed: ' . $response->reason());
//            return;
//        }
//
//        $responseBody = $response->body(); // returns a string
//        Log::info('sms http response: ' . $responseBody);

        // this line of code will be replaced by the details from the gateway server
        $serverRef = generateRandomNumber();

        $payment->update([
            'server_ref' => $serverRef
        ]);


    }
}
