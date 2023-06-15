<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CommissionConfig;
use App\Models\ConfigLoanOverdueStage;
use App\Models\Configuration;
use App\Models\User;
use App\Traits\CusboardingPageTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class GeneralConfigurationController extends Controller
{
    use CusboardingPageTrait;

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function getLoanStagesConfigurations(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $configs = ConfigLoanOverdueStage::with([])->get();
        // get configurations
        return response()->json(ApiResponse::successResponseWithData($configs));

    }


    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function updateLoanStagesConfigurations(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $config = ConfigLoanOverdueStage::with([])->find($id);
        if(blank($config)) {
            throw new \Exception("Invalid config id: $id");
        }
        // get configurations
        $config->update($request->all());
        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function addLoanStagesConfigurations(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $this->validate($request,  [
            'jargon' => 'required',
//            'from_days_after_deadline' => 'required|int',
//            'to_days_after_deadline' => 'required|int',
            'interest_percentage_per_day' => 'required',
//            'installment_enabled' => 'required|bool',
//            'auto_deduction_enabled' => 'required|bool',
            'percentage_raise_on_next_loan_request' => 'required',
//            'eligible_for_next_loan_request' => 'required',
        ]);

        $jargon = $request->get('jargon');
        $desc = $request->get('desc');
        $fromDaysAfterDeadline = $request->get('from_days_after_deadline');
        $toDaysAfterDeadline = $request->get('to_days_after_deadline');
        $interestPercentagePerDay = $request->get('interest_percentage_per_day');
        $installmentEnabled = $request->get('installment_enabled') ?: false;
        $autoDeductionEnabled = $request->get('auto_deduction_enabled') ?: false;
        $percentageRaiseOnNextLoanRequest = $request->get('percentage_raise_on_next_loan_request');
        $eligibleForNextLoanRequest = $request->get('eligible_for_next_loan_request') ?: true;

        $lastConfig = ConfigLoanOverdueStage::with([])->orderByDesc('name')->first();
        $name =  $lastConfig->{'name'} + 1;

        ConfigLoanOverdueStage::with([])->create([
            'name' => $name,
            'desc' => $desc,
            'from_days_after_deadline' => $fromDaysAfterDeadline,
            'to_days_after_deadline' => $toDaysAfterDeadline,
            'interest_percentage_per_day' => $interestPercentagePerDay,
            'installment_enabled' => $installmentEnabled,
            'auto_deduction_enabled' => $autoDeductionEnabled,
            'percentage_raise_on_next_loan_request' => $percentageRaiseOnNextLoanRequest,
            'eligible_for_next_loan_request' => $eligibleForNextLoanRequest,
            'jargon' => $jargon
        ]);
        // get configurations

        // create a permission for it.
        $permissionName =  "access to loan stage $name";
        Permission::with([])->updateOrCreate([
            'guard_name' => 'web',
            'name' => $permissionName
        ]);

        return response()->json(ApiResponse::successResponseWithMessage());

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function removeLoanStagesConfiguration($id): \Illuminate\Http\JsonResponse
    {

        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $config = ConfigLoanOverdueStage::with([])->find($id);
        if(blank($config)){
            throw new \Exception("invalid id");
        }
        $name = $config->{'name'};
        if($name == "0"){
            throw new \Exception("This stage cannot be deleted");
        }
        $permissionName =  "access to loan stage $name";
//        $exits = User::permission($permissionName)->exists();
//        if($exits) {
//            $users = User::permission($permissionName)->exists();
//            foreach ($users as $user) {
//                $user->revokePermissionTo($permissionName);
//            }
//        }

        Permission::with([])->where('name', $permissionName)->delete();
        $config->delete();
        return response()->json(ApiResponse::successResponseWithMessage());
    }

    /**
     * @throws AuthorizationException
     */
    public function getConfigurations(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        // get configurations

        $config = Configuration::with([])->first();
        return response()->json(ApiResponse::successResponseWithData($config));

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function updateConfigurations(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $config = Configuration::with([])->find($id);
        if(blank($config)) {
            throw new \Exception("Invalid config id: $id");
        }
        // get configurations
        $config->update($request->all());
        return response()->json(ApiResponse::successResponseWithMessage());

    }


    /**
     * @throws AuthorizationException
     */
    public function getCommissionConfigurations(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        // get configurations

        $config = CommissionConfig::with([])->first();
        return response()->json(ApiResponse::successResponseWithData($config));

    }

    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function updateCommissionConfigurations(Request $request, $stageName): \Illuminate\Http\JsonResponse
    {
        $this->authorize('configureLoanApplicationParameters', Configuration::class);

        $stage = ConfigLoanOverdueStage::with([])->where('name','');


        $configs = CommissionConfig::with([])->where('stage_name', '=', $stageName)->get();
        if(empty($configs)) {
            CommissionConfig::with([])->create($request->all());
        }
        // get configurations
        CommissionConfig::with([])->where('stage_name', '=', $stageName)->update($request->all());
        return response()->json(ApiResponse::successResponseWithMessage());

    }
}
