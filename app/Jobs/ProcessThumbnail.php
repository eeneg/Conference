<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;

class ProcessThumbnail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $file;

    public function __construct($file)
    {
        $this->file = $file;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $file = $this->file;

        try{
            $py = Process::path(app_path('/Python/'))->forever()->run('python3 Thumbnail.py '.escapeshellarg('Temp_File_Storage/'.$file->hash_name));
        }catch(Exception $e){

        }

        $py->throw();

        $res = $py->output();
        $er = $py->errorOutput();

        if($res){
            $file->thumbnail()->create(['base64_thumbnail' => $res]);
        }else{

        }

    }
}
