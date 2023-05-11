<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigLoanOverdueStage;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Traits\LoanApplicationTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\FlareClient\Api;
use Spatie\Permission\Models\Permission;

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

    public function fetchLoanStages(Request $request): \Illuminate\Http\JsonResponse
    {

        $user = $request->user();


        $loanStages = ConfigLoanOverdueStage::with([])->get();
        if(!$user->hasRole('super admin')) {
            // get all permission of this admin

            $permissionNames = $user->getPerssionNames();

            // filter the loanStages with these permissions

            $loanStages = $loanStages->filter(function ($stage) use ($permissionNames) {
                $stageName = $stage->{'name'};
               return collect($permissionNames)->contains("access to loan stage $stageName");
            });

        }

        return response()->json(ApiResponse::successResponseWithData($loanStages));

    }

}
