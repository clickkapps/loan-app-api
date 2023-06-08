<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use App\Events\PermissionAssigned;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

trait RolePermissionTrait
{
    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function assign(Request $request): \Illuminate\Http\JsonResponse
    {

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


        return response()->json(ApiResponse::successResponseWithMessage());

    }

}
