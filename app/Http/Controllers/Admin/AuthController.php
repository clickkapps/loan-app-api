<?php

namespace App\Http\Controllers\Admin;

use App\Classes\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AuthTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->middleware('throttle:3,5')->only(['login']); // 3(maxAttempts).  // 5(decayMinutes)
    }

}
