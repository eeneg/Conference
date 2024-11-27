<?php

namespace App\Services;

use App\Models\File;
use App\Jobs\ProcessFileContent;
use App\Jobs\ProcessThumbnail;
use Illuminate\Support\Facades\Bus;

class FileContentService {



    public function handle($id){
        $file = File::find($id);
        Bus::chain([
            new ProcessThumbnail($file),
            new ProcessFileContent($file)
        ])->dispatch();
    }
}
