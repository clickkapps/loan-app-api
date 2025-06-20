<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigLoanOverdueStage;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatus;
use App\Traits\LoanApplicationTrait;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
            $loans = LoanApplication::with(['latestStatus', 'assignedTo'])
                ->where('loan_overdue_stage_id', '=', null)
                ->where('closed','=', false)
                ->get();
//            $loans = $this->getLoansWhoseLatestStatusIs(status: 'requested');

        }else {

            $items = explode('-', $type);
            $stageName = $items[1];


            $this->authorize("access to loan stage $stageName");

            // get loans whose latest stage is $stageName

            $stage = ConfigLoanOverdueStage::with([])->where('name', $stageName)->first();
            Log::info('days_to_deadline: ' . json_encode($request->get('days_to_deadline')));
            if($stageName == '0' && !blank($request->get('days_to_deadline'))){

                $daysToDeadline = $request->get('days_to_deadline');
                $deadline = Carbon::now()->addDays($daysToDeadline);
                $loans = LoanApplication::with(['latestStatus', 'assignedTo'])
                    ->where('loan_overdue_stage_id', '=', $stage->{'id'})
                    ->where('closed','=', false)
                    ->whereDate('deadline', '<=', $deadline)->get();
            }else{
                $loans = LoanApplication::with(['latestStatus', 'assignedTo'])
                    ->where('loan_overdue_stage_id', '=', $stage->{'id'})
                    ->where('closed','=', false)
                    ->get();
//                $loans = $this->getLoansWhoseLatestStatusIs(status: $type);
            }


//            $loans = LoanApplication::with([])->where('loan_overdue_stage_id', $stage->{'id'})->get();
        }

        return response()->json(ApiResponse::successResponseWithData($loans));

    }

    /**
     * @throws ValidationException
     */
    public function fetchLoanHistory(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
           'start_date' => 'required|date',
           'end_date' => 'required|date',
        ]);

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $loanData = LoanApplication::with(['latestStatus', 'stage', 'assignedTo'])
            ->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->where('closed', '=', true)
            ->orderByDesc('created_at')->get();

        return response()->json(ApiResponse::successResponseWithData($loanData));
    }

    /**
     * @throws \Exception
     */
    public function fetchLoanDetail(Request $request, $loanId): \Illuminate\Http\JsonResponse
    {
        $loan = LoanApplication::with(['statuses', 'assignedTo', 'stage'])->find($loanId);
        if(blank($loan)){
            throw  new \Exception('Loan not found');
        }

        return response()->json(ApiResponse::successResponseWithData($loan));

    }

    /**
     * @throws \Exception
     */
    public function waveLoanInterest(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
           'loan_id' => 'required',
           'wave_interest_percentage' => 'required'
        ]);

        $loanId = $request->get('loan_id');
        $waveInterestPercentage = $request->get('wave_interest_percentage');

        $loan = LoanApplication::with(['statuses', 'assignedTo'])->find($loanId);
        if(blank($loan)){
            throw  new \Exception('Loan not found');
        }

        $loan->update([
            'wave_interest_by_percentage' => $waveInterestPercentage
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());

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
                        'value' => "stage-$stageName",
                        'id' => $stage->{'id'},
                        'db_name' => $stage->{'name'}
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
                    'value' => "stage-$stageName",
                    'id' => $stage->{'id'},
                    'db_name' => $stage->{'name'}
                ];

            });



        }

        return response()->json(ApiResponse::successResponseWithData($response));

    }



}
