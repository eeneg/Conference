<?php

namespace App\Jobs;

use App\Models\PdfContent;
use App\Models\File;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ProcessFileContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle()
    {
        $file = $this->file;

        try{
            $py = Process::path(app_path('/Python/'))->forever()->run('python3 app.py '.escapeshellarg('Temp_File_Storage/'.$file->hash_name));
        }catch(Exception $e){
            $this->logError($file->id, $e, 'File Error on try/catch');
        }

        $res = $py->output();
        $er = $py->errorOutput();

        if($res){
            $file->pdfContent()->create(['content' => $res]);
            $this->uploadToBlob($file);
        }else{
            $this->logError($file->id, $er, 'File Content Unreadable');
        }

        $file->update(['processed' => true]);

    }

    public function logError($file_id, $verbose, $remark) : void{

        File::find($file_id)->fileError()->create(['verbose' => $verbose, 'remark' => $remark]);

    }

    public function uploadToBlob($file){
        $pdf = Storage::disk('local')->get('public/Temp_File_Storage/'.$file->hash_name);

        try{
            $upload = Storage::put('file_uploads/'.$file->hash_name, $pdf);
        }catch(Exception $e){
            File::find($file->file_id)->fileError()->create(['verbose' => $e, 'remark' => 'File upload to blob failed']);
        }

        $delete = Storage::disk('local')->delete('public/Temp_File_Storage/'.$file->hash_name);
    }
}
