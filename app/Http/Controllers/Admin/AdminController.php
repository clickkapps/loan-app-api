<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // add new administrator
    /**
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function create(Request $request) {

        // check if user can create new admin
        $this->authorize('create', Admin::class);

        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);

        $name = $request->get('name');
        $email = $request->get('email');

        $userExists = User::where('email', $email)->exists();

        if($userExists) {
            throw new \Exception("Account with this email $email already exist");
        }

        $tempPassword = generateRandomNumber();

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make($tempPassword),
            'active' => true,
        ]);

        $admin->refresh();
        $admin->assignRole(['admin']);

        // Notify user about account created and temporal password

        Log::info("new admin created: $email");

        return response()->json(ApiResponse::successResponse('Account created'));

    }

    /**
     * @throws AuthorizationException
     */
    public function getAll() {

        $this->authorize('viewAny', Admin::class);
        return User::role('admin')->get();

    }

}
