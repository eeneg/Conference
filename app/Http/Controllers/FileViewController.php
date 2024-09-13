<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileViewController extends Controller
{
    public function fileView($id){

        $file = File::find($id)->only('hash_name');

        $pdf = Storage::disk('azure')->get('file_uploads/'.$file['hash_name']);

        return base64_encode($pdf);
    }

    public function referenceView($id){

        $file = Reference::find($id);

        $pdf = Storage::get('reference_files/'.$file['hash_name']);

        return base64_encode($pdf);
    }
}
