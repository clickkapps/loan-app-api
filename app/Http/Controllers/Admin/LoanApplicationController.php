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

class LoanApplicationController extends Controller
{
    use LoanApplicationTrait;

    /**
     * @throws AuthorizationException
     */
    public function fetchLoans(Request $request, $type): \Illuminate\Http\JsonResponse
    {

        if($type == "pending") {

            $this->authorize('access to pending loans');

            // Get loans whose latest status is requested --------
            $loans = $this->getLoansWhoseLatestStatusIs(status: 'requested');

        }else {

            $items = explode('-', $type);
            $stageName = $items[1];

            // get loans whose latest stage is $stageName

//            $stage = ConfigLoanOverdueStage::with([])->where('name', $stageName)->first();
            $loans = $this->getLoansWhoseLatestStatusIs(status: $type);
//            $loans = LoanApplication::with([])->where('loan_overdue_stage_id', $stage->{'id'})->get();
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


        $response = [];
        $loanStages = ConfigLoanOverdueStage::with([])->get();

        if(!$user->hasRole('super admin')) {
            // get all permission of this admin

            $permissionNames = collect($user->getPermissionNames());

            // filter the loanStages with these permissions

            if($permissionNames->contains('access to pending loans')) {
                $response[] = [
                    'name' => 'Pending loans',
                    'value' => 'pending'
                ];
            }

            $loanStages->each(function ($stage) use ($permissionNames, &$response) {
                $stageName = $stage->{'name'};
                $contains = $permissionNames->contains("access to loan stage $stageName");
                if($contains) {
                    $response[] = [
                        'name' => $stage->{'jargon'},
                        'value' => "stage-$stageName"
                    ];
                }

            });



        }else {

            $response[] = [
                'name' => 'Pending loans',
                'value' => 'pending'
            ];

            // super admin -------
            $loanStages->each(function ($stage) use (&$response) {

                $stageName = $stage->{'name'};
                $response[] = [
                    'name' => $stage->{'jargon'},
                    'value' => "stage-$stageName"
                ];

            });



        }

        return response()->json(ApiResponse::successResponseWithData($response));

    }



}
