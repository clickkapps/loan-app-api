<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function getLoansWhoseLatestStatusIs(string $status): \Illuminate\Database\Eloquent\Collection|array
    {
        return LoanApplication::with(['latestStatus'])->whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('loan_application_statuses')
                ->groupBy('loan_application_id');
        })
            ->whereHas('statuses', function ($query) {
                $query->where('status', '=', 'requested');
            })
            ->get();
    }

    public function initiateLoanDisbursal(LoanApplication $loan): void
    {
        // check if there's enough funds in the merchant
    }
}
