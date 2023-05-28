<?php

namespace App\Exceptions;

use App\Classes\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e)
    {
        Log::error('['.$e->getCode().'] "'.$e->getMessage().'" on line '.$e->getTrace()[0]['line'].' of file '.$e->getTrace()[0]['file']);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $exception) {
        });

        $this->renderable(function (\Exception $e, Request $request) {

            if ($request->is('*/api/*')) {
                return response()->json(ApiResponse::failedResponse($e->getMessage()));
            }
        });
    }


}
