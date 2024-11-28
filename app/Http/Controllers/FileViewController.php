<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Reference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class FileViewController extends Controller
{
    public function fileView($id){

        $file = File::find($id)->only(['id', 'hash_name']);
        $filePath = 'file_uploads/' . $file['hash_name'];

        $cacheKey = $file['id'];

        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);

            return response($cachedData, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Length' => strlen($cachedData),
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            ]);
        }

        $stream = Storage::readStream($filePath);
        $fileData = stream_get_contents($stream);

        Cache::put($cacheKey, $fileData, now()->addMinutes(60));

        fclose($stream);

        return response($fileData, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($fileData),
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    }
}
