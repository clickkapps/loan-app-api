<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Traits\AuthTrait;

class AuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->middleware('throttle:3,5')->only(['login']); // 3(maxAttempts).  // 5(decayMinutes)
    }
}
