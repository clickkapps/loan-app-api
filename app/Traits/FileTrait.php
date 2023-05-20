<?php

namespace App\Traits;

use App\Classes\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait FileTrait
{
    /**
     * @throws ValidationException
     */
    public function uploadFile(Request $request): \Illuminate\Http\JsonResponse
    {

        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');

        // save file in s3 and return link to file
        $path = Storage::putFile('kyc', $file);

        $fullPath =  $this->getSignedUrl($path);

        return response()->json(ApiResponse::successResponseWithData(
            [
                'display_path' => $fullPath,
                'path' => $path
            ]
        ));
    }

    public function getSignedUrl(string $path, int $minutes = 5): string
    {
        return Storage::temporaryUrl(
            $path, now()->addMinutes($minutes)
        );
    }
}
