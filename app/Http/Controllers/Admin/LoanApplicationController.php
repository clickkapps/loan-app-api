<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Traits\LoanApplicationTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanApplicationController extends Controller
{
    use LoanApplicationTrait;

    /**
     * @throws AuthorizationException
     */
    public function fetchLoans(Request $request, $type): \Illuminate\Http\JsonResponse
    {

        $loans = collect();
        if($type == "pending") {

            $this->authorize('access to pending loans');

            // Get loans whose latest status is requested --------
            $loans = $this->getLoansWhoseLatestStatusIs(status: 'requested');

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

    public function getLoanStages(Request $request) {

        $user = $request->user();

    }

}
