<?php

namespace App\Services;

use App\Models\File;
use App\Jobs\ProcessFileContent;
use App\Jobs\ProcessThumbnail;

class FileContentService {



    public function handle($id){
        $file = File::find($id);
        ProcessFileContent::dispatch($file);
        ProcessThumbnail::dispatch($file);
    }
}
