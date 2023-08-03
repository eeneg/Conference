<?php

namespace App\Actions;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class SaveAttachments
{
    public function __invoke($attachments, $title, $conference): Attachment
    {
        $attachments = collect($attachments)->map(function($file) use ($title){
            @[$category, $files] = $file;

            if (empty($files)) {
                return [
                    'category' => $category,
                    'files' => [],
                ];
            }

            return [
                'category' => $category,
                'files' => collect($files)
                    ->filter(fn ($file) => is_file($file))
                    ->each(fn ($file) => Storage::putFileAs(str("public/$title/$category")->slug(), $file, str($file->getClientOriginalName())->slug()))
                    ->map(fn ($file) => ['path' => str("$title/$category")->slug(), 'fileName' => str($file->getClientOriginalName())->slug()])
                    ->toArray()
            ];
        });

        return Attachment::create([
            'conference_id' => $conference,
            'files' => $attachments->toArray()
        ]);
    }
}
