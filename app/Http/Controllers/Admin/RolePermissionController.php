<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Events\PermissionAssigned;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function assign(Request $request): \Illuminate\Http\JsonResponse
    {

        // the logged in user must have permission to assign permissions
        // For a start only super admin has that permission
        $this->authorize('assignPermissions', Permission::class);


        $request->validate([
           'user_id' => 'required',
           'permission' => 'required|string',
           'status' => 'required|in:assign,revoke'
        ]);

        $userId = $request->get('user_id');
        $permission = $request->get('permission');
        $status = $request->get('status');

        $user = User::find($userId);

        if(blank($user)){
            Log::info("user not found: $userId");
            throw new Exception('user not found');
        }

        if($status == 'assign') {

            $user->givePermissionTo($permission);
            PermissionAssigned::dispatch($user, $permission, true);

        }else {

            // else revoke
            $user->revokePermissionTo($permission);
            PermissionAssigned::dispatch($user, $permission, false);

        }




        return response()->json(ApiResponse::successResponse());

    }


    /**
     * @throws AuthorizationException
     */
    public function getAll(Request $request): \Illuminate\Http\JsonResponse
    {
        // the logged in user must have permission to view permissions
        // For a start only super admin has that permission

        $this->authorize('viewPermissions', Permission::class);

        $filter = $request->get('filter');

        $permissions = Permission::all();

        $filtered = $permissions->filter(function ($model) use ($filter) {
            if($filter == 'major') {
                return str_contains($model->name, 'manage');
            }else if($filter == 'sub') {
                return !str_contains($model->name, 'manage');
            }
            return true;

        })->values();
        return response()->json(ApiResponse::successResponseV2($filtered));

    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function getUserPermissions(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        // the logged in user must have permission to view permissions
        // For a start only super admin has that permission

        $this->authorize('viewPermissions', Permission::class);

        $filter = $request->get('filter');

        $user = User::find($userId);

        if(blank($user)){
            Log::info("user not found: $userId");
            throw new Exception('user not found');
        }

        $permissions = $user->permissions;

        $filtered = $permissions->filter(function ($model) use ($filter) {
            if($filter == 'major') {
                return str_contains($model->name, 'manage');
            }else if($filter == 'sub') {
                return !str_contains($model->name, 'manage');
            }
            return true;

        })->values();

        return response()->json(ApiResponse::successResponseV2($filtered));

    }
}
