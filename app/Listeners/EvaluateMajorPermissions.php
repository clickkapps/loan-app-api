<?php

namespace App\Listeners;

use App\Events\PermissionAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class EvaluateMajorPermissions
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PermissionAssigned $event): void
    {
        $user = $event->user;
        $permission = $event->permission;
        $assigned = $event->assigned;
        $adminPermissions = collect(config('custom.admin_permissions'));

        // add this permission to list of permissions on the fly ----
        if(str_contains($permission, 'loan stage')) {

            $mp = $adminPermissions->firstWhere('major', 'manage loans applications');
            $updatedSubs = collect($mp['subs'])->concat($permission);
            $mp->update(['subs' => $updatedSubs]);

            $permissionMap = $mp;

        } else {

            $permissionMap = $adminPermissions->firstWhere(function ($item) use ($permission) {
                return collect($item['subs'])->contains($permission);
            });

        }



        if(blank($permissionMap)){
            return;
        }

        $majorPermission = $permissionMap['major'];
        $subs = $permissionMap['subs'];

        if($assigned) {
            // make sure the major menu is checked
            if(!$user->hasPermissionTo($majorPermission)) {
                $user->givePermissionTo($majorPermission);
            }

        } else {

            // else check if all the sub menus are revoked and uncheck the major menu
            if(!$user->hasAnyPermission($subs)){
                $user->revokePermissionTo($majorPermission);
            }
        }

        $user->refresh();
        $userPermissionNames = json_encode($user->getPermissionNames());
        Log::info("user permissions: $userPermissionNames");



    }


}
