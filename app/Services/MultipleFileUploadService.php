<?php

namespace App\Services;

use App\Jobs\ProcessFileContent;
use App\Jobs\ProcessThumbnail;
use App\Models\File;
use Ramsey\Uuid\Uuid;
use App\Models\FileVersionControl;
use Illuminate\Support\Facades\Bus;

class MultipleFileUploadService {
    public function handle($files){
        $data = [];
        $files = File::whereIn('id', $files->select('id')->flatten())->get();
        foreach($files as $file){
            array_push($data, ['id' => (string) Uuid::uuid4(), 'control_id' => (string) Uuid::uuid4(), 'file_id' => $file->id]);
            Bus::chain([
                new ProcessThumbnail($file),
                new ProcessFileContent($file)
            ])->dispatch();

        }
        FileVersionControl::insert($data);
    }
}
