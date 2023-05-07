<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\LoanApplication;
use Illuminate\Http\Request;

trait LoanApplicationTrait
{

    public function getLoanApplicationUpdate(Request $request): \Illuminate\Http\JsonResponse
    {

        $runningLoan = LoanApplication::with([])->where('closed',false)->first();
        if(blank($runningLoan)){
            return response()->json(ApiResponse::successResponseWithData(
                [
                    'loan' => null,
                ]
            ));
        }

        $applicationStatus = $runningLoan->latestStatus;
        $displayStatus = $applicationStatus->status;
        if($displayStatus == "requested") {
            $displayStatus = "Pending approval";
        }else if($displayStatus == "disbursed") {
            $displayStatus = "Loan disbursed";
        }else if($displayStatus == "denied") {
            $displayStatus = "Application denied";
        }

        return response()->json(ApiResponse::successResponseWithData(
            [
                'loan' => $runningLoan,
                'display_status' => $displayStatus,
                'status' => $applicationStatus->status
            ]
        ));


    }
}
