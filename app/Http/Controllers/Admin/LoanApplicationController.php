<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function fetchLoans(Request $request, $type): \Illuminate\Http\JsonResponse
    {

        $loans = collect();
        if($type == "pending") {

            $this->authorize('access to pending loans');

            $pendingLoanIds = LoanApplicationStatus::with([])->where('status', 'requested')->pluck('loan_application_id');
            $loans = LoanApplication::with(['latestStatus'])->whereIn('id', $pendingLoanIds)->orderByDesc('created_at')->get();

        }

        return response()->json(ApiResponse::successResponseWithData($loans));

    }

    /**
     * @throws \Exception
     */
    public function fetchLoanDetail(Request $request, $loanId): \Illuminate\Http\JsonResponse
    {
        $loan = LoanApplication::with(['statuses'])->find($loanId);
        if(blank($loan)){
            throw  new \Exception('Loan not found');
        }

        return response()->json(ApiResponse::successResponseWithData($loan));

    }
}
