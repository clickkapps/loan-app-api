<?php

namespace App\Http\Controllers;

use App\Events\PaymentCallbackReceived;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function paymentCallback(Request $request)
    {

        $responseCode = $request->get('responseCode');
        $responseMessage = $request->get('message');
        $data = $request->get('data');
        $clientRef = $data['clientReference'];
        $serverRef = $data['transactionId'];

        $query = Payment::with([])
            ->where('client_ref','=', $clientRef)
            ->where('server_ref', '=',  $serverRef);

        $payment = (clone $query)->first();
        if($payment->{'response_code'} == $responseCode && $payment->{'status'} == 'closed') {
            Log::info('customLog: payment callback received with new response code when record is already closed: ---- ');
            $previousStatus = [
                'response_code' => $payment->{'response_code'},
                'response_message' => $payment->{'response_message'}
            ];
            $newStatus = [
                'response_code' => $responseCode,
                'response_message' => $responseMessage
            ];

            Log::info('previous status: ----------------');
            Log::info(json_encode($previousStatus));

            Log::info('new status: ----------------');
            Log::info(json_encode($newStatus));

            return;
        }

        (clone $query)->update([
            'response_message' => $responseMessage,
            'response_code' => $responseCode,
            'status' => 'closed'
        ]);

        $payment = (clone $query)->first();


        // after updating the payment  record, dispatch event

        PaymentCallbackReceived::dispatch($payment);

        return response()->json(['message' => 'received'], 200);


    }
}
