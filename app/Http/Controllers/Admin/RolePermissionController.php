<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Events\PermissionAssigned;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\RolePermissionTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{

    use RolePermissionTrait;


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
        return response()->json(ApiResponse::successResponseWithData($filtered));

    }

    /**
     * @throws AuthorizationException
     */
    public function getLoanStagesPermissions(Request $request): \Illuminate\Http\JsonResponse
    {
        // the logged in user must have permission to view permissions
        // For a start only super admin has that permission

        $this->authorize('viewPermissions', Permission::class);

        $permissions = Permission::all();

        $filtered = $permissions->filter(function ($model) {
            return str_contains($model->name, 'access to loan stage');
        })->values();
        return response()->json(ApiResponse::successResponseWithData($filtered));

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

        return response()->json(ApiResponse::successResponseWithData($filtered));

    }
}
