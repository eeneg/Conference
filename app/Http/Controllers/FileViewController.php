<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Reference;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class FileViewController extends Controller
{
    public function fileView($id){

        $file = File::find($id)->only('hash_name');
        $filePath = 'file_uploads/' . $file['hash_name'];

        if (!Storage::exists($filePath)) {
            abort(404, 'File not found');
        }

        $fileStream = Storage::readStream($filePath);
        $fileMimeType = Storage::mimeType($filePath);
        $fileSize = Storage::size($filePath);

        return Response::stream(function () use ($fileStream) {
            fpassthru($fileStream);
        }, 200, [
            'Content-Type' => $fileMimeType,
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    }

    public function referenceView($id){

        $file = Reference::find($id);

        $pdf = Storage::get('reference_files/'.$file['hash_name']);

        return base64_encode($pdf);
    }
}
